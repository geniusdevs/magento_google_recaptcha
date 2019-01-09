<?php

/**
 * Created by PhpStorm.
 * User: grzegorzkopec
 * Date: 10/08/2018
 * Time: 14:20
 */
class GeniusDev_ReCaptcha_Helper_Data extends Mage_Captcha_Helper_Data
{
    const RECAPTCHA_ENABLED_XML_PATH = 'geniusdev_recaptcha/settings/enabled';
    const RECAPTCHA_PRIVATE_KEY_XML_PATH = 'geniusdev_recaptcha/settings/private_key';
    const RECAPTCHA_PUBLIC_KEY_XML_PATH = 'geniusdev_recaptcha/settings/public_key';
    const RECAPTCHA_VERIFICATION_URL_XML_PATH = 'geniusdev_recaptcha/settings/verification_url';
    const RECAPTCHA_THEME_XML_PATH = 'geniusdev_recaptcha/settings/theme';
    const RECAPTCHA_LANG_XML_PATH = 'geniusdev_recaptcha/settings/lang';
    const RECAPTCHA_API_URL_XML_PATH = 'geniusdev_recaptcha/settings/api_url';

    protected $zendCaptchaJs = 'mage/captcha.js';

    public function getCaptcha($formId)
    {
        if (!array_key_exists($formId, $this->_captcha)) {
            if ($this->isRecaptchaEnabled()) {
                $this->_captcha[$formId] = Mage::getModel('geniusdev_recaptcha/google', array('formId' => $formId));
            } else {
                $type = $this->getConfigNode('type');
                $this->_captcha[$formId] = Mage::getModel('captcha/' . $type, array('formId' => $formId));
            }
        }

        return $this->_captcha[$formId];
    }

    public function isRecaptchaEnabled()
    {
        return Mage::getStoreConfigFlag(self::RECAPTCHA_ENABLED_XML_PATH);
    }

    public function getRecaptchaPrivateKey()
    {
        return Mage::getStoreConfig(self::RECAPTCHA_PRIVATE_KEY_XML_PATH);
    }

    public function getRecaptchaPublicKey()
    {
        return Mage::getStoreConfig(self::RECAPTCHA_PUBLIC_KEY_XML_PATH);
    }

    public function getRecaptchaTheme()
    {
        return Mage::getStoreConfig(self::RECAPTCHA_THEME_XML_PATH);
    }

    public function getRecaptchaLanguage()
    {
        return Mage::getStoreConfig(self::RECAPTCHA_LANG_XML_PATH);
    }

    public function getRecaptchaVerificationUrl()
    {
        return Mage::getStoreConfig(self::RECAPTCHA_VERIFICATION_URL_XML_PATH);
    }

    public function getRecaptchaApiUrl()
    {
        return Mage::getStoreConfig(self::RECAPTCHA_API_URL_XML_PATH);
    }

    public function isAdditionalUsageEnabled($formId)
    {
        return Mage::getStoreConfigFlag('geniusdev_recaptcha/usage/' . $formId);
    }

    public function getJsUrl()
    {
        if ($this->isRecaptchaEnabled()) {
            return $this->getRecaptchaApiJsUrl();
        } else {
            return Mage::getBaseUrl('js') . $this->zendCaptchaJs;
        }
    }

    public function getRecaptchaApiJsUrl()
    {
        $lang = $this->getRecaptchaLanguage();
        $url = $this->getRecaptchaApiUrl();

        return $url . ((strlen($lang)) ? ('?hl=' . $lang) : '');
    }
}