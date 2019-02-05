<?php

namespace NL\NlAuth\Type;


use TYPO3\CMS\Core\Type\Enumeration;

class UserTokenType extends Enumeration
{
    const PASSWORD_RECOVERY = 'passwordRecovery';

    const CONFIRMATION = 'confirmation';

    const APPROVEMENT = 'approvement';
}