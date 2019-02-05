<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'NL.NlAuth',
        'User',
        [
            'Auth' => 'showLoginForm, showLogoutForm',
            'Recovery' => 'showPasswordResetRequestForm, showPasswordResetForm, requestPasswordReset, passwordReset',
            'Registration' => 'showRegistrationForm, register, confirm, resend, approve',
            'Profile' => 'edit, update, delete',
        ],
        // non-cacheable actions
        [
            'Auth' => 'showLoginForm, showLogoutForm',
            'Recovery' => 'showPasswordResetRequestForm, showPasswordResetForm, requestPasswordReset, passwordReset',
            'Registration' => 'showRegistrationForm, register, confirm, resend, approve',
            'Profile' => 'edit, update, delete',
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    user {
                        iconIdentifier = nl_auth-plugin-user
                        title = LLL:EXT:nl_auth/Resources/Private/Language/locallang_db.xlf:tx_nl_auth_user.name
                        description = LLL:EXT:nl_auth/Resources/Private/Language/locallang_db.xlf:tx_nl_auth_user.description
                        tt_content_defValues {
                            CType = list
                            list_type = nlauth_user
                        }
                    }
                }
                show = *
            }
       }'
    );

    // Cache configuration
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nlauth_user_token'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nlauth_user_token'] = [
            'backend' => \NL\NlAuth\Cache\UserTokenDatabaseBacked::class,
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'groups' => [
                'system',
            ],
        ];
    }

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

    $iconRegistry->registerIcon(
        'nl_auth-plugin-user',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:nl_auth/Resources/Public/Icons/user_plugin_user.svg']
    );

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        NL\NlAuth\Controller\AuthController::class,
        NL\NlAuth\Type\AuthSignalType::AFTER_SUCCESSFUL_LOGIN,
        NL\NlAuth\Slots\RedirectUrlSlot::class,
        'processRedirect',
        true
    );
    $signalSlotDispatcher->connect(
        NL\NlAuth\Controller\AuthController::class,
        NL\NlAuth\Type\AuthSignalType::AFTER_FAILED_LOGIN,
        NL\NlAuth\Slots\RedirectUrlSlot::class,
        'processRedirect',
        true
    );
    $signalSlotDispatcher->connect(
        NL\NlAuth\Controller\AuthController::class,
        NL\NlAuth\Type\AuthSignalType::AFTER_LOGOUT,
        NL\NlAuth\Slots\RedirectUrlSlot::class,
        'processRedirect',
        true
    );
    $signalSlotDispatcher->connect(
        NL\NlAuth\Controller\AuthController::class,
        NL\NlAuth\Type\AuthSignalType::ALREADY_LOGGED_IN,
        NL\NlAuth\Slots\RedirectUrlSlot::class,
        'processRedirect',
        true
    );

    /**
     * XCLASSes
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Sv\AuthenticationService::class] = array(
        'className' => \NL\NlAuth\Authentication\AuthenticationService::class
    );
});
