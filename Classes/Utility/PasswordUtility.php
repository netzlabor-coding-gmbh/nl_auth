<?php

namespace NL\NlAuth\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        if (version_compare(TYPO3_version, 9.5, '>=')) {
            /* @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface $hashInstance */
            $hashInstance = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');
            $password = $hashInstance->getHashedPassword($password);
        } else {
            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
                if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')) {
                    $saltingInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
                    $password = $saltingInstance->getHashedPassword($password);
                }
            }
        }

        return $password;
    }
}