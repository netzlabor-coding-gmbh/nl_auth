<?php

namespace NL\NlAuth\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;

/**
 * Class PasswordUtility
 * @package NL\NlAuth\Utility
 */
class PasswordUtility
{
    /**
     * @param $password
     * @return string
     */
    public static function hashPassword($password)
    {
        if (ExtensionManagementUtility::isLoaded('saltedpasswords')) {
            if (SaltedPasswordsUtility::isUsageEnabled('FE')) {
                $saltingInstance = SaltFactory::getSaltingInstance();
                $password = $saltingInstance->getHashedPassword($password);
            }
        }

        return $password;
    }
}