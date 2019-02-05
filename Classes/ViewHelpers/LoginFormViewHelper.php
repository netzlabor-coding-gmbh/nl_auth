<?php

namespace NL\NlAuth\ViewHelpers;


use NL\NlAuth\SettingsTrait;
use NL\NlAuth\Type\LoginType;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use NL\NlAuth\ViewHelpers\Form\AbstractAuthFormViewHelper;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class LoginFormViewHelper extends AbstractAuthFormViewHelper
{
    use SettingsTrait;

    /**
     * @var array
     */
    protected $submitJavaScriptCode = array();

    /**
     * @var array
     */
    protected $additionalHiddenFields = array();

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    protected $settings;

    protected $frameworkConfiguration;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;

        $this->frameworkConfiguration = $this
            ->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $this->settings = $this
            ->configurationManager
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                $this->frameworkConfiguration['extensionName'],
                $this->frameworkConfiguration['pluginName']
            );
    }

    /**
     *
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('userStoragePageUid', 'integer', 'User Storage Page', true);
    }

    /**
     * Gets additional code for login forms based on the
     * TYPO3_CONF_VARS/EXTCONF/felogin/loginFormOnSubmitFuncs hook
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $formData = GeneralUtility::_GET();
        ArrayUtility::mergeRecursiveWithOverrule($formData, GeneralUtility::_POST());

        $this->additionalHiddenFields[] =
            '<input type="hidden" name="return_url" value="' . $formData['return_url'] . '" />';
        $this->additionalHiddenFields[] =
            '<input type="hidden" name="redirect_url" value="' . $formData['redirect_url'] . '" />';

        if (GeneralUtility::inList($this->getSettingsValue('login.redirectMode'), 'referer')
            || GeneralUtility::inList($this->getSettingsValue('login.redirectMode'), 'refererDomains')) {

            $referer = $formData['referer'] ?: GeneralUtility::getIndpEnv('HTTP_REFERER');

            if ($referer) {
                $this->additionalHiddenFields[] =
                    '<input type="hidden" name="referer" value="' . htmlspecialchars($referer) . '" />';
                if ($formData['redirectReferrer'] === 'off') {
                    $this->additionalHiddenFields[] = '<input type="hidden" name="redirectReferrer" value="off" />';
                }
            }
        }

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'])) {
            $parameters = array();
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] as $callback) {
                list($js, $hiddenFields) = GeneralUtility::callUserFunction($callback, $parameters, $this);
                if (isset($js)) {
                    $this->submitJavaScriptCode[] = $js;
                }
                if (isset($hiddenFields)) {
                    $this->additionalHiddenFields[] = $hiddenFields;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->setFormActionUri();
        $this->setFormMethod();
        $this->setFormOnSubmit();
        $content = $this->renderHiddenLoginTypeField(LoginType::LOGIN);
        $content .= $this->renderHiddenUserStoragePageUidField($this->arguments['userStoragePageUid']);
        $content .= $this->renderAdditionalHiddenFields();
        $content .= $this->renderChildren();
        $this->tag->setContent($content);
        return $this->tag->render();
    }

    /**
     * @return void
     */
    protected function setFormOnSubmit()
    {
        $this->tag->addAttribute('onsubmit', implode(';', $this->submitJavaScriptCode) . '; return true;');
    }

    /**
     * @param int $userStoragePageUid
     * @return string
     */
    protected function renderHiddenUserStoragePageUidField($userStoragePageUid)
    {
        return LF . '<input type="hidden" name="pid" value="' . $userStoragePageUid . '" />' . LF;
    }

    /**
     * @return string
     */
    protected function renderAdditionalHiddenFields()
    {
        return LF . implode(LF, $this->additionalHiddenFields);
    }
}