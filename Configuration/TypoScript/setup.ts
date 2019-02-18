
plugin.tx_nlauth_user {
    view {
        templateRootPaths.0 = EXT:nl_auth/Resources/Private/Templates/
        templateRootPaths.1 = {$plugin.tx_nlauth_user.view.templateRootPath}
        partialRootPaths.0 = EXT:nl_auth/Resources/Private/Partials/
        partialRootPaths.1 = {$plugin.tx_nlauth_user.view.partialRootPath}
        layoutRootPaths.0 = EXT:nl_auth/Resources/Private/Layouts/
        layoutRootPaths.1 = {$plugin.tx_nlauth_user.view.layoutRootPath}
    }
    persistence {
        storagePid = {$plugin.tx_nlauth_user.persistence.storagePid}
        #recursive = 1
    }
    features {
        #skipDefaultArguments = 1
        # if set to 1, the enable fields are ignored in BE context
        ignoreAllEnableFieldsInBe = 0
        # Should be on by default, but can be disabled if all action in the plugin are uncached
        requireCHashArgumentForActionArguments = 0
    }
    mvc {
        #callDefaultActionIfActionCantBeResolved = 1
    }
    settings {
        ajaxTypeNum = {$plugin.tx_nlauth_user.settings.ajaxTypeNum}
        dateFormat = {$plugin.tx_nlauth_user.settings.dateFormat}

        login {
            page = {$plugin.tx_nlauth_user.settings.login.page}
            showForgotPasswordLink = {$plugin.tx_nlauth_user.settings.login.showForgotPasswordLink}
            showRegistrationLink = {$plugin.tx_nlauth_user.settings.login.showRegistrationLink}
            showPermaLogin = {$plugin.tx_nlauth_user.settings.login.showPermaLogin}
            showLogoutFormAfterLogin = {$plugin.tx_nlauth_user.settings.login.showLogoutFormAfterLogin}
            redirectMode = {$plugin.tx_nlauth_user.settings.login.redirectMode}
            redirectFirstMethod = {$plugin.tx_nlauth_user.settings.login.redirectFirstMethod}
            redirectPageLogin = {$plugin.tx_nlauth_user.settings.login.redirectPageLogin}
            redirectPageLoginError = {$plugin.tx_nlauth_user.settings.login.redirectPageLoginError}
            redirectPageLogout = {$plugin.tx_nlauth_user.settings.login.redirectPageLogout}
            redirectDisable = {$plugin.tx_nlauth_user.settings.login.redirectDisable}
            domains = {$plugin.tx_nlauth_user.settings.login.domains}
        }

        passwordRecovery {
            page = {$plugin.tx_nlauth_user.settings.passwordRecovery.page}
            token {
                lifetime = {$plugin.tx_nlauth_user.settings.passwordRecovery.token.lifetime}
            }
            loginOnSuccess = {$plugin.tx_nlauth_user.settings.passwordRecovery.loginOnSuccess}
            showLoginLink = {$plugin.tx_nlauth_user.settings.passwordRecovery.showLoginLink}
            redirectPageReset = {$plugin.tx_nlauth_user.settings.passwordRecovery.redirectPageReset}
            redirectDisable = {$plugin.tx_nlauth_user.settings.passwordRecovery.redirectDisable}
        }

        registration {
            page = {$plugin.tx_nlauth_user.settings.registration.page}
            fields = {$plugin.tx_nlauth_user.settings.registration.fields}
            takeEmailAsUsername = {$plugin.tx_nlauth_user.settings.registration.takeEmailAsUsername}
            overrideUserGroup = {$plugin.tx_nlauth_user.settings.registration.overrideUserGroup}
            confirmation {
                enable = {$plugin.tx_nlauth_user.settings.registration.confirmation.enable}
                loginOnSuccess = {$plugin.tx_nlauth_user.settings.registration.confirmation.loginOnSuccess}
                tokenLifetime = {$plugin.tx_nlauth_user.settings.registration.confirmation.tokenLifetime}
            }
            approvement {
                enable = {$plugin.tx_nlauth_user.settings.registration.approvement.enable}
                adminMailList = {$plugin.tx_nlauth_user.settings.registration.approvement.adminMailList}
                tokenLifetime = {$plugin.tx_nlauth_user.settings.registration.approvement.tokenLifetime}
                assignGroup = {$plugin.tx_nlauth_user.settings.registration.approvement.assignGroup}
                multiple = {$plugin.tx_nlauth_user.settings.registration.approvement.multiple}
                availableGroups = {$plugin.tx_nlauth_user.settings.registration.approvement.availableGroups}
                declineGroup = {$plugin.tx_nlauth_user.settings.registration.approvement.declineGroup}
                tokenLifetime = {$plugin.tx_nlauth_user.settings.registration.approvement.tokenLifetime}
            }
            redirectPageRegistration = {$plugin.tx_nlauth_user.settings.registration.redirectPageRegistration}
            redirectPageConfirmation = {$plugin.tx_nlauth_user.settings.registration.redirectPageConfirmation}
            redirectDisable = {$plugin.tx_nlauth_user.settings.registration.redirectDisable}

            notifications {
                welcome = {$plugin.tx_nlauth_user.settings.registration.notifications.welcome}
                approve = {$plugin.tx_nlauth_user.settings.registration.notifications.approve}
            }
        }

        profile {
            page = {$plugin.tx_nlauth_user.settings.profile.page}
            fields = {$plugin.tx_nlauth_user.settings.profile.fields}
            takeEmailAsUsername = {$plugin.tx_nlauth_user.settings.profile.takeEmailAsUsername}
            deletion {
                enable = {$plugin.tx_nlauth_user.settings.profile.deleting.enable}
                hard = {$plugin.tx_nlauth_user.settings.profile.deleting.hard}
                notifyAdmin = {$plugin.tx_nlauth_user.settings.profile.deleting.notify}
                adminMailList = {$plugin.tx_nlauth_user.settings.profile.deleting.adminMailList}
            }
        }

        mail {
            fromEmail = {$plugin.tx_nlauth_user.settings.mail.fromEmail}
            fromName = {$plugin.tx_nlauth_user.settings.mail.fromName}
            passwordRecoverySubject = {$plugin.tx_nlauth_user.settings.mail.passwordRecoverySubject}
            welcomeSubject = {$plugin.tx_nlauth_user.settings.mail.welcomeSubject}
            confirmationSubject = {$plugin.tx_nlauth_user.settings.mail.confirmationSubject}
            approvementSubject = {$plugin.tx_nlauth_user.settings.mail.approvementSubject}
            approveStatusSubject = {$plugin.tx_nlauth_user.settings.mail.approveStatusSubject}
        }

        validation {
            registration.register.user {
                _self {
                    1 = \NL\NlAuth\Domain\Validator\PasswordValidator
                    2 = \NL\NlAuth\Domain\Validator\UniqueFrontendUserValidator(property='username')
                    3 = \NL\NlAuth\Domain\Validator\UniqueFrontendUserValidator(property='email')
                }
                username {
                    1 = NotEmpty
                    2 = StringLength(minimum=3, maximum=255)
                    3 = Alphanumeric
                }
                email {
                    1 = NotEmpty
                    2 = EmailAddress
                }
                password {
                    1 = NotEmpty
                    2 = String
                    3 = StringLength(minimum=8, maximum=20)
                }
                passwordRepeat {
                    1 = NotEmpty
                    2 = String
                    3 = StringLength(minimum=8, maximum=20)
                }
            }
            profile.update.user {
                _self {
                    1 = \NL\NlAuth\Domain\Validator\PasswordValidator
                    2 = \NL\NlAuth\Domain\Validator\UniqueFrontendUserValidator(property='username', dirty=true)
                    3 = \NL\NlAuth\Domain\Validator\UniqueFrontendUserValidator(property='email', dirty=true)
                }
                passwordRepeat {
                    1 = String
                    2 = StringLength(minimum=8, maximum=20)
                }
                password {
                    1 = String
                }
                username {
                    1 = NotEmpty
                    2 = StringLength(minimum=3, maximum=255)
                    3 = Alphanumeric
                }
                email {
                    1 = NotEmpty
                    2 = EmailAddress
                }
            }
        }
    }
}

# these classes are only used in auto-generated templates
plugin.tx_nlauth._CSS_DEFAULT_STYLE (
    textarea.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    input.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    .tx-nl-auth table {
        border-collapse:separate;
        border-spacing:10px;
    }

    .tx-nl-auth table th {
        font-weight:bold;
    }

    .tx-nl-auth table td {
        vertical-align:top;
    }

    .typo3-messages .message-error {
        color:red;
    }

    .typo3-messages .message-ok {
        color:green;
    }
)
