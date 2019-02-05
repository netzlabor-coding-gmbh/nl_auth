<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'NL.NlAuth',
            'User',
            'User'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('nl_auth', 'Configuration/TypoScript', 'Auth');

        if (TYPO3_MODE === 'BE') {
            $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

            $iconRegistry->registerIcon(
                'nl_auth-plugin-user',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                ['source' => 'EXT:nl_auth/Resources/Public/Icons/user_plugin_user.svg']
            );
        }
    }
);
