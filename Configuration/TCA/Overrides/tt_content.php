<?php
defined('TYPO3_MODE') || die();

call_user_func(function()
{
    /**
     * Temporary variables
     */
    $extensionKey = 'nl_auth';

    /**
     * Register the "User" plugin
     */
    $userPluginSignature = str_replace('_', '', $extensionKey) . '_user';

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'NL.' . $extensionKey,
        'User',
        'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_db.xlf:tt_content.list_type.' . $userPluginSignature,
        'EXT:' . $extensionKey . '/Resources/Public/Icons/user_plugin_user.svg'
    );

    // Disable the display of layout and select_key fields for the plugins
    // provided by the extension
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$userPluginSignature] = 'layout,select_key,pages,recursive';

    // Activate the display of the plug-in flexform field and set FlexForm definition
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$userPluginSignature] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $userPluginSignature, 'FILE:EXT:' . $extensionKey . '/Configuration/FlexForms/flexform_' . $userPluginSignature . '.xml'
    );
});
