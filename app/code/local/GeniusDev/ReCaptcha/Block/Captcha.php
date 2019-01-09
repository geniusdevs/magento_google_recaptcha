<?php

/**
 * Created by PhpStorm.
 * User: grzegorzkopec
 * Date: 10/08/2018
 * Time: 14:24
 */
class GeniusDev_ReCaptcha_Block_Captcha extends Mage_Captcha_Block_Captcha
{
    protected $captchaBlock;
    protected $captchaModel;

    /**
     * @param $formId
     * @return $this
     */
    public function setFormId($formId)
    {
        $this->setData('form_id', $formId);
        $this->addJsLibrary();

        return $this;
    }

    /**
     * If captcha is enabled add JS library to head block
     */
    protected function addJsLibrary()
    {
        if (!$this->getCaptchaModel()->isRequired() || Mage::getDesign()->getArea() == 'adminhtml') {
            return;
        }
        if (Mage::helper('captcha')->isRecaptchaEnabled()) {
            $this->addGoogleJsLibrary();
        } else {
            $this->addZendJsLibrary();
        }
    }

    /**
     * Default Magento captcha JS
     */
    protected function addZendJsLibrary()
    {
        $head = $this->getLayout()->getBlock('head');
        $head->addJs('mage/captcha.js');
    }

    /**
     * Google re-captcha
     */
    protected function addGoogleJsLibrary()
    {
        $apiUrl = Mage::helper('geniusdev_recaptcha')->getRecaptchaApiJsUrl();
        $block = $this->getLayout()->createBlock('core/text')
            ->setText('<script src="'.$apiUrl.'" async defer></script>');
        $head = $this->getLayout()->getBlock('head');
        $head->append($block);
        $head->removeItem('js', 'mage/captcha.js');
    }

    /**
     * Render captcha block
     * @return string
     */
    protected function _toHtml()
    {
        $captchaBLock = $this->getCaptchaBlock();
        $captchaBLock->setData($this->getData());

        return $captchaBLock->toHtml();
    }

    /**
     * Return captcha block depends on type
     * @return Mage_Core_Block_Abstract|mixed
     */
    protected function getCaptchaBlock()
    {
        if (!$this->captchaBlock) {
            $this->captchaBlock = $this->getLayout()->createBlock($this->getCaptchaModel()->getBlockName());
        }

        return $this->captchaBlock;
    }

    /**
     * @return Mage_Captcha_Model_Interface|mixed
     */
    protected function getCaptchaModel()
    {
        if (!$this->captchaModel) {
            $this->captchaModel = Mage::helper('captcha')->getCaptcha($this->getFormId());
        }

        return $this->captchaModel;
    }
}