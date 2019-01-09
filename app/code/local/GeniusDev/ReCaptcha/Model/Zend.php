<?php

/**
 * Created by PhpStorm.
 * User: grzegorzkopec
 * Date: 10/08/2018
 * Time: 11:17
 */
class GeniusDev_ReCaptcha_Model_Zend extends Mage_Captcha_Model_Zend
{

    public function getCaptchaField($request, $formId = null)
    {
        $captchaParams = $request->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
        return $captchaParams[$formId];
    }
}