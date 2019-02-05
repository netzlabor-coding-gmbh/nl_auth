<?php

namespace NL\NlAuth;


use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

trait SettingsTrait
{
    /**
     * Shorthand helper for getting setting values with optional default values
     * Any setting value is automatically processed via stdWrap if configured.
     *
     * @param string $settingPath Path to the setting, e.g. "foo.bar.qux"
     * @param mixed $defaultValue Default value if no value is set
     * @return mixed
     */
    protected function getSettingsValue($settingPath, $defaultValue = null)
    {
        $value = ObjectAccess::getPropertyPath($this->settings, $settingPath);
        $stdWrapConfiguration = ObjectAccess::getPropertyPath($this->settings, $settingPath . '.stdWrap');
        if ($stdWrapConfiguration !== null) {
            $value = $this->getTypoScriptFrontendController()->cObj->stdWrap($value, $stdWrapConfiguration);
        }
        // Change type of value to type of default value if possible
        if (!empty($value) && $defaultValue !== null) {
            settype($value, gettype($defaultValue));
        }
        $value = !empty($value) ? $value : $defaultValue;
        return $value;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}