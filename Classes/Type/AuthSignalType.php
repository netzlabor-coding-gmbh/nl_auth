<?php

namespace NL\NlAuth\Type;


use TYPO3\CMS\Core\Type\Enumeration;

class AuthSignalType extends Enumeration
{
    const AFTER_SUCCESSFUL_LOGIN = 'afterSuccessfulLogin';

    const AFTER_FAILED_LOGIN = 'afterFailedLogin';

    const AFTER_LOGOUT = 'afterLogout';

    const ALREADY_LOGGED_IN = 'alreadyLoggedIn';
}