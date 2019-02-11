
plugin.tx_nlauth_user {
    view {
        # cat=plugin.tx_nlauth_user/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:nl_auth/Resources/Private/Templates/
        # cat=plugin.tx_nlauth_user/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:nl_auth/Resources/Private/Partials/
        # cat=plugin.tx_nlauth_user/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:nl_auth/Resources/Private/Layouts/
    }
    persistence {
        # cat=plugin.tx_nlauth_user//a; type=string; label=Default storage PID
        storagePid =
    }
    settings {
        // cat=plugin.tx_nlauth_user//dateFormat; type=string; label= Date format: Format for date output (PHP date/strftime), value of TYPO3_CONF_VARS/SYS/ddmmyy if empty
        dateFormat = d-m-y
        login {
            // cat=plugin.tx_nlauth_user/login/page; type=int+; label= Login page ID: Page where visitors can log in, current page if empty
            page =
            // cat=plugin.tx_nlauth_user/login/showForgotPasswordLink; type=boolean; label= Display Password Recovery Link: If set, the section in the template to display the link to the forget password dialogue is visible.
            showForgotPasswordLink = 0
            // cat=plugin.tx_nlauth_user/login/showRegistrationLink; type=boolean; label= Display Registration Link: If set, the section in the template to display the link to the registration is visible.
            showRegistrationLink = 0
            // cat=plugin.tx_nlauth_user/login/showPermaLogin; type=boolean; label= Display Remember Login Option: If set, the section in the template to display the option to remember the login (with a cookie) is visible.
            showPermaLogin = 0
            // cat=plugin.tx_nlauth_user/login/showLogoutFormAfterLogin; type=boolean; label= Disable redirect after successful login, but display logout-form: If set, the logout form will be displayed immediately after successful login.
            showLogoutFormAfterLogin = 0
            // cat=plugin.tx_nlauth_user/login/redirectMode; type=string; label= Redirect Mode: Comma separated list of redirect modes. Possible values: groupLogin, userLogin, login, getpost, referer, refererDomains, loginError, logout
            redirectMode = login,logout
            // cat=plugin.tx_nlauth_user/login/redirectFirstMethod; type=boolean; label= Use First Supported Mode from Selection: If set the first method from redirectMode which is possible will be used
            redirectFirstMethod = 0
            // cat=plugin.tx_nlauth_user/login/redirectPageLogin; type=int+; label= After Successful Login Redirect to Page: Page id to redirect to after Login
            redirectPageLogin =
            // cat=plugin.tx_nlauth_user/login/redirectPageLoginError; type=int+; label= After Failed Login Redirect to Page: Page id to redirect to after Login Error
            redirectPageLoginError =
            // cat=plugin.tx_nlauth_user/login/redirectPageLogout; type=int+; label= After Logout Redirect to Page: Page id to redirect to after Logout
            redirectPageLogout =
            // cat=plugin.tx_nlauth_user/login/redirectDisable; type=boolean; label= Disable Redirect: If set redirecting is disabled
            redirectDisable = 0
            // cat=plugin.tx_nlauth_user/login/domains; type=string; label= Allowed Referrer-Redirect-Domains: Comma separated list of domains which are allowed for the referrer redirect mode
            domains =
        }

        passwordRecovery {
            // cat=plugin.tx_nlauth_user/recovery/page; type=int+; label=Password recovery page ID: Page where a password recovery can be performed, current page if empty
            page =
            token {
                // cat=plugin.tx_nlauth_user/recovery/token.lifetime; type=int+; label=Recovery token lifetime: Number of seconds a password recovery token is valid
                lifetime = 86400
            }
            // cat=plugin.tx_nlauth_user/recovery/loginOnSuccess; type=boolean; label=Login on success: Whether to automatically log in users after successful password recovery
            loginOnSuccess = 0
            showLoginLink = 0
            redirectPageReset =
            redirectDisable = 0
        }

        registration {
            page =
            fields =
            takeEmailAsUsername = 0
            overrideUserGroup = 0
            confirmation {
                enable = 0
                loginOnSuccess = 0
                tokenLifetime = 86400
            }
            approvement {
                enable = 0
                adminMailList =
                assignGroup = 0
                multiple = 0
                availableGroups =
                declineGroup =
                tokenLifetime = 86400
            }
            redirectPageRegistration =
            redirectPageConfirmation =
            redirectDisable = 0
            notifications {
                welcome = 0
                approve = 0
            }
        }
        profile {
            page =
            fields =
            takeEmailAsUsername = 0
            deletion {
                enable = 0
                hard = 0
                notifyAdmin = 0
                adminMailList =
            }
        }
        mail {
            // cat=plugin.tx_nlauth_user/mail/fromEmail; type=string; label= Mail from email: Sender for the password reset mail, system fromEmail address if empty
            fromEmail =
            // cat=plugin.tx_nlauth_user/mail/fromName; type=string; label= Mail from name: Sender for the password reset mail, system fromName address if empty
            fromName =
            // cat=plugin.tx_nlauth_user/mail/passwordRecoverySubject; type=string; label= Mail password recovery subject: Subject for the password recovery mail
            passwordRecoverySubject =
            welcomeSubject =
            confirmationSubject =
            approvementSubject =
            approveStatusSubject =
        }
    }
}
