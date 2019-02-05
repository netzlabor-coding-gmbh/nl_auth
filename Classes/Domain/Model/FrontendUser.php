<?php

namespace NL\NlAuth\Domain\Model;


use NL\NlAuth\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser as ExtbaseFrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class FrontendUser
 * @package NL\NlAuth\Domain\Model
 */
class FrontendUser extends ExtbaseFrontendUser
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $www;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $passwordRepeat;

    /**
     * @var FrontendUserGroupRepository
     */
    protected $usergroupRepository;

    /**
     * @var bool
     */
    protected $disable;

    /**
     * @var int
     */
    protected $txNlAuthUserRedirectpid;

    /**
     * @var \DateTime|NULL
     */
    protected $txNlAuthUserConfirmedat;

    /**
     * @var \DateTime|NULL
     */
    protected $txNlAuthUserApprovedat;

    /**
     * @var \DateTime|NULL
     */
    protected $txNlAuthUserDeclinedat;

    /**
     * @param FrontendUserGroupRepository $usergroupRepository
     */
    public function injectFrontendUserGroupRepository(FrontendUserGroupRepository $usergroupRepository)
    {
        $this->usergroupRepository = $usergroupRepository;
    }

    /**
     * @return $this
     */
    public function takeEmailAsUsername()
    {
        $this->setUsername($this->getEmail());
        return $this;
    }

    /**
     * Sets usergroup as new Object Storage
     * @return $this
     */
    public function removeAllUsergroups()
    {
        $this->usergroup = new ObjectStorage();
        return $this;
    }

    /**
     * @param array $uids
     * @return $this
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function addUsergroupsByUids($uids)
    {
        $usergroups = $this->usergroupRepository->findByUids($uids);

        foreach ($usergroups as $group) {
            $this->addUsergroup($group);
        }

        return $this;
    }

    /**
     * @param array $uids
     * @return $this
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function overrideUsergroupsByUids($uids)
    {
        return $this->removeAllUsergroups()->addUsergroupsByUids($uids);
    }

    /**
     * @param bool $checkApproved
     * @return $this
     * @throws \Exception
     */
    public function setConfirmed($checkApproved = false)
    {
        $this->setTxNlAuthUserConfirmedat(new \DateTime('NOW'));

        if (!$checkApproved || $this->txNlAuthUserApprovedat) {
            $this->setDisable(false);
        }

        return $this;
    }

    /**
     * @param bool $checkConfirmed
     * @return $this
     * @throws \Exception
     */
    public function setApproved($checkConfirmed = false)
    {
        if (!$this->txNlAuthUserApprovedat) {
            $this->setTxNlAuthUserApprovedat(new \DateTime('NOW'));
        }

        if (!$checkConfirmed || $this->txNlAuthUserConfirmedat) {
            $this->setDisable(false);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setDeclined()
    {
        $this->setTxNlAuthUserDeclinedat(new \DateTime('NOW'));
        $this->setDisable(true);

        return $this;
    }

    /**
     * @param boolean $disable
     * @return $this
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
        return $this;
    }
    /**
     * @return boolean
     */
    public function getDisable()
    {
        return $this->disable;
    }

    /**
     * @param int $txNlAuthUserRedirectpid
     * @return $this
     */
    public function setTxNlAuthUserRedirectpid($txNlAuthUserRedirectpid)
    {
        $this->txNlAuthUserRedirectpid = $txNlAuthUserRedirectpid;
        return $this;
    }

    /**
     * @return int
     */
    public function getTxNlAuthUserRedirectpid()
    {
        return $this->txNlAuthUserRedirectpid;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getTxNlAuthUserConfirmedat()
    {
        return $this->txNlAuthUserConfirmedat;
    }

    /**
     * @param \DateTime $txNlAuthUserConfirmedat
     * @api
     * @return $this
     */
    public function setTxNlAuthUserConfirmedat(\DateTime $txNlAuthUserConfirmedat)
    {
        $this->txNlAuthUserConfirmedat = $txNlAuthUserConfirmedat;
        return $this;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getTxNlAuthUserApprovedat()
    {
        return $this->txNlAuthUserApprovedat;
    }

    /**
     * @param \DateTime $txNlAuthUserApprovedat
     * @api
     * @return $this
     */
    public function setTxNlAuthUserApprovedat(\DateTime $txNlAuthUserApprovedat)
    {
        $this->txNlAuthUserApprovedat = $txNlAuthUserApprovedat;
        return $this;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getTxNlAuthUserDeclinedat()
    {
        return $this->txNlAuthUserDeclinedat;
    }

    /**
     * @param \DateTime $txNlAuthUserDeclinedat
     * @api
     * @return $this
     */
    public function setTxNlAuthUserDeclinedat(\DateTime $txNlAuthUserDeclinedat)
    {
        $this->txNlAuthUserDeclinedat = $txNlAuthUserDeclinedat;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordRepeat()
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     * @return $this
     */
    public function setPasswordRepeat($passwordRepeat)
    {
        $this->passwordRepeat = $passwordRepeat;
        return $this;
    }
}