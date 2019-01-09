<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Captcha
 * @copyright  Copyright (c) 2006-2018 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of Zend_Captcha
 *
 * @category   Mage
 * @package    Mage_Captcha
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class GeniusDev_ReCaptcha_Model_Google implements Mage_Captcha_Model_Interface
{
    /**
     * Captcha form id
     * @var string
     */
    protected $_formId;

    protected $captchaField = 'g-recaptcha-response';

    /**
     * Mage_Captcha_Model_Google constructor.
     * @param $params
     * @throws Exception
     */
    public function __construct($params)
    {
        if (!isset($params['formId'])) {
            throw new Exception('formId is mandatory');
        }
        $this->_formId = $params['formId'];
    }

    /**
     * Returns key with respect of current form ID
     *
     * @param string $key
     * @return string
     */
    protected function _getFormIdKey($key)
    {
        return $this->_formId . '_' . $key;
    }

    /**
     * Get Block Name
     *
     * @return string
     */
    public function getBlockName()
    {
        return 'geniusdev_recaptcha/captcha_google';
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param null|string $login
     * @return bool
     */
    public function isRequired($login = null)
    {
        if(!$this->_isUserAuth() && $this->additionalUsageEnabled()){
            return true;
        }
        if ($this->_isUserAuth() || !$this->_isEnabled() || !in_array($this->_formId, $this->_getTargetForms())) {
            return false;
        }

        return ($this->_isShowAlways() || $this->_isOverLimitAttempts($login)
            || $this->getSession()->getData($this->_getFormIdKey('show_captcha'))
        );
    }

    public function additionalUsageEnabled(){
        return $this->_getHelper()->isAdditionalUsageEnabled($this->_formId);
    }

    /**
     * Check is overlimit attempts
     *
     * @param string $login
     * @return bool
     */
    protected function _isOverLimitAttempts($login)
    {
        return ($this->_isOverLimitIpAttempt() || $this->_isOverLimitLoginAttempts($login));
    }

    /**
     * Returns number of allowed attempts for same login
     *
     * @return int
     */
    protected function _getAllowedAttemptsForSameLogin()
    {
        return (int)$this->_getHelper()->getConfigNode('failed_attempts_login');
    }

    /**
     * Returns number of allowed attempts from same IP
     *
     * @return int
     */
    protected function _getAllowedAttemptsFromSameIp()
    {
        return (int)$this->_getHelper()->getConfigNode('failed_attempts_ip');
    }

    /**
     * Check is overlimit saved attempts from one ip
     *
     * @return bool
     */
    protected function _isOverLimitIpAttempt()
    {
        $countAttemptsByIp = Mage::getResourceModel('captcha/log')->countAttemptsByRemoteAddress();

        return $countAttemptsByIp >= $this->_getAllowedAttemptsFromSameIp();
    }

    /**
     * Is Over Limit Login Attempts
     *
     * @param string $login
     * @return bool
     */
    protected function _isOverLimitLoginAttempts($login)
    {
        if ($login != false) {
            $countAttemptsByLogin = Mage::getResourceModel('captcha/log')->countAttemptsByUserLogin($login);

            return ($countAttemptsByLogin >= $this->_getAllowedAttemptsForSameLogin());
        }

        return false;
    }

    /**
     * Check is user auth
     *
     * @return bool
     */
    protected function _isUserAuth()
    {
        return Mage::app()->getStore()->isAdmin()
            ? Mage::getSingleton('admin/session')->isLoggedIn()
            : Mage::getSingleton('customer/session')->isLoggedIn();
    }


    /**
     * Checks whether captcha was guessed correctly by user
     *
     * @param string $word
     * @param string $privateKey
     * @return bool
     */
    public function isCorrect($word, $privateKey = null)
    {
        if (!$word) {
            return false;
        }

        return $this->isCaptchaValid($word, $privateKey);
    }

    protected function isCaptchaValid($captchaResponse, $privateKey = null)
    {
        $result = false;
        $params = array(
            'secret' => $this->_getHelper()->getRecaptchaPrivateKey(),
            'response' => $captchaResponse,
            'remoteip' => Mage::helper('core/http')->getRemoteAddr()
        );
        $url = $this->_getHelper()->getRecaptchaVerificationUrl();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $requestResult = trim(curl_exec($ch));
        curl_close($ch);

        if (is_array(json_decode($requestResult, true))) {
            $response = json_decode($requestResult, true);

            if (isset($response['success']) && $response['success'] === true) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Returns session instance
     *
     * @return Mage_Customer_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('customer/session');
    }


    /**
     * log Attempt
     *
     * @param string $login
     * @return Mage_Captcha_Model_Zend
     */
    public function logAttempt($login)
    {
        if ($this->_isEnabled() && in_array($this->_formId, $this->_getTargetForms())) {
            Mage::getResourceModel('captcha/log')->logAttempt($login);
            if ($this->_isOverLimitLoginAttempts($login)) {
                $this->getSession()->setData($this->_getFormIdKey('show_captcha'), 1);
            }
        }

        return $this;
    }

    /**
     * Returns captcha helper
     *
     * @return Mage_Captcha_Helper_Data
     */
    protected function _getHelper()
    {
        if (empty($this->_helper)) {
            $this->_helper = Mage::helper('geniusdev_recaptcha');
        }

        return $this->_helper;
    }


    /**
     * Whether to show captcha for this form every time
     *
     * @return bool
     */
    protected function _isShowAlways()
    {
        // setting the allowed attempts to 0 is like setting mode to always
        if ($this->_getAllowedAttemptsForSameLogin() == 0 || $this->_getAllowedAttemptsFromSameIp() == 0) {
            return true;
        }

        if ((string)$this->_getHelper()->getConfigNode('mode') == Mage_Captcha_Helper_Data::MODE_ALWAYS) {
            return true;
        }

        $alwaysFor = $this->_getHelper()->getConfigNode('always_for');
        foreach ($alwaysFor as $nodeFormId => $isAlwaysFor) {
            if ($isAlwaysFor && $this->_formId == $nodeFormId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether captcha is enabled at this area
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return (string)$this->_getHelper()->getConfigNode('enable');
    }

    /**
     * Retrieve list of forms where captcha must be shown
     *
     * For frontend this list is based on current website
     *
     * @return array
     */
    protected function _getTargetForms()
    {
        $formsString = (string)$this->_getHelper()->getConfigNode('forms');

        return explode(',', $formsString);
    }

    /**
     * Generates captcha
     *
     * @return void
     */
    public function generate()
    {
        // TODO: Implement generate() method.
    }

    public function getCaptchaField($request, $formId = null)
    {
        return $request->getPost($this->captchaField);
    }
}
