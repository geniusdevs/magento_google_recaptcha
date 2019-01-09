<?php

class GeniusDev_ReCaptcha_Model_Source_Google_Lang {
    /**
     * Generate lang options array
     * @return array
     */
    public function toOptionArray() {
        return array(
            array(
                'value' => '',
                'label' => 'Auto-detects the user\'s language',
            ),
            array(
                'value' => 'en',
                'label' => 'English (default)',
            ),
            array(
                'value' => 'nl',
                'label' => 'Dutch',
            ),
            array(
                'value' => 'fr',
                'label' => 'French',
            ),
            array(
                'value' => 'de',
                'label' => 'German',
            ),
            array(
                'value' => 'pt',
                'label' => 'Portuguese',
            ),
            array(
                'value' => 'ru',
                'label' => 'Russian',
            ),
            array(
                'value' => 'es',
                'label' => 'Spanish',
            ),
            array(
                'value' => 'tr',
                'label' => 'Turkish',
            ),
        );
    }
}