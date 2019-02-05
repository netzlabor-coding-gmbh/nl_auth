<?php

namespace NL\NlAuth\Authentication;


use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService
{
    /**
     * Initialize authentication service
     *
     * @param string $mode Subtype of the service which is used to call the service.
     * @param array $loginData Submitted login form data
     * @param array $authInfo Information array. Holds submitted form data etc.
     * @param AbstractUserAuthentication $pObj Parent object
     */
    public function initAuth($mode, $loginData, $authInfo, $pObj)
    {
        if (TYPO3_MODE === 'FE') {
            if (filter_var($loginData['uname'], FILTER_VALIDATE_EMAIL)) {
                $authInfo['db_user']['username_column'] = 'email';
            }
        }

        parent::initAuth($mode, $loginData, $authInfo, $pObj);
    }
}