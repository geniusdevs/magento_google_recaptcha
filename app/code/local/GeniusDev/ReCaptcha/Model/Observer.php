<?php

/**
 * Created by PhpStorm.
 * User: grzegorzkopec
 * Date: 10/08/2018
 * Time: 10:33
 */
class GeniusDev_ReCaptcha_Model_Observer extends Mage_Captcha_Model_Observer
{
    protected function _getCaptchaString($request, $formId)
    {
        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);

        return $captchaModel->getCaptchaField($request, $formId);
    }

    public function contactFormPredispatch($observer)
    {
        $formId = 'contacts';
        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                Mage::getSingleton('customer/session')->addError(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
                Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                Mage::app()->getResponse()->sendResponse();exit;
            }
        }
    }
}