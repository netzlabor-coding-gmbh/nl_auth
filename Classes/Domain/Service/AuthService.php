<?php

namespace NL\NlAuth\Domain\Service;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Core\SingletonInterface;

use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class AuthService implements SingletonInterface
{
    /**
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @var FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontedUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * @param FrontendUserGroupRepository $frontendUserGroupRepository
     */
    public function injectFrontendUserGroupRepository(FrontendUserGroupRepository $frontendUserGroupRepository)
    {
        $this->frontendUserGroupRepository = $frontendUserGroupRepository;
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn()
    {
        return $this->getTypoScriptFrontendController()->loginUser;
    }

    /**
     * @param FrontendUser $user
     * @return void
     */
    public function login(FrontendUser $user)
    {
        $tsfe = $this->getTypoScriptFrontendController();
        $tsfe->fe_user->createUserSession($user->_getCleanProperties());
        $tsfe->fe_user->loginSessionStarted = true;
        $tsfe->fe_user->user = $tsfe->fe_user->fetchUserSession();
        $tsfe->loginUser = true;
    }

    /**
     * @return object
     */
    public function getUser()
    {
        return $this->frontendUserRepository->findByIdentifier(
            $this->getTypoScriptFrontendController()->fe_user->user['uid']
        );
    }

    /**
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getUserGroups()
    {
        return $this->frontendUserGroupRepository->findByUids(
            $this->getTypoScriptFrontendController()->fe_user->groupData['uid']
        );
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}