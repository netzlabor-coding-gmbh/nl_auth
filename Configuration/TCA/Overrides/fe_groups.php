<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    // Adds the redirect field to the fe_groups table
    $additionalColumns = [
        'tx_nlauth_user_redirectPid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_auth/Resources/Private/Language/locallang_db.xlf:tx_nlauth_user_redirectPid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ]
        ]
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', $additionalColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'tx_nlauth_user_redirectPid', '', 'after:TSconfig');
});
