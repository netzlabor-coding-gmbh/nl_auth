<?php

namespace NL\NlAuth\Type;


use TYPO3\CMS\Core\Type\Enumeration;

class LoginType extends Enumeration
{
    /**
     * @var string
     */
    const LOGIN = 'login';

    /**
     * @var string
     */
    const LOGOUT = 'logout';
}