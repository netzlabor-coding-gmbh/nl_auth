<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    // Adds the redirect field and the forgotHash field to the fe_users-table
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
        ],
        'tx_nlauth_user_confirmedAt' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_auth/Resources/Private/Language/locallang_db.xlf:tx_nlauth_user_confirmedAt',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ],
        'tx_nlauth_user_approvedAt' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_auth/Resources/Private/Language/locallang_db.xlf:tx_nlauth_user_approvedAt',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ],
        'tx_nlauth_user_declinedAt' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_auth/Resources/Private/Language/locallang_db.xlf:tx_nlauth_user_declinedAt',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ]
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $additionalColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_nlauth_user_redirectPid', '', 'after:TSconfig');
});
