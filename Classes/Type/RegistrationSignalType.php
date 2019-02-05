<?php

namespace NL\NlAuth\Type;


use TYPO3\CMS\Core\Type\Enumeration;

class RegistrationSignalType extends Enumeration
{
    const AFTER_SUCCESSFUL_REGISTRATION = 'afterSuccessfulRegistration';

    const AFTER_SUCCESSFUL_CONFIRMATION = 'afterSuccessfulConfirmation';

    const AFTER_FAILED_CONFIRMATION = 'afterFailedConfirmation';

    const AFTER_SUCCESSFUL_APPROVEMENT = 'afterSuccessfulApprovement';

    const AFTER_FAILED_APPROVEMENT = 'afterFailedApprovement';
}