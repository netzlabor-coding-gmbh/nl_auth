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
    protected $txNlauthUserRedirectpid;

    /**
     * @var \DateTime|NULL
     */
    protected $txNlauthUserConfirmedat;

    /**
     * @var \DateTime|NULL
     */
    protected $txNlauthUserApprovedat;

    /**
     * @var \DateTime|NULL
     */
    protected $txNlauthUserDeclinedat;

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
        $this->setTxNlauthUserConfirmedat(new \DateTime());

        if (!$checkApproved || $this->txNlauthUserApprovedat) {
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
        if (!$this->txNlauthUserApprovedat) {
            $this->setTxNlauthUserApprovedat(new \DateTime());
        }

        if (!$checkConfirmed || $this->txNlauthUserConfirmedat) {
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
        $this->setTxNlauthUserDeclinedat(new \DateTime());
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
     * @param int $txNlauthUserRedirectpid
     * @return $this
     */
    public function setTxNlauthUserRedirectpid($txNlauthUserRedirectpid)
    {
        $this->txNlauthUserRedirectpid = $txNlauthUserRedirectpid;
        return $this;
    }

    /**
     * @return int
     */
    public function getTxNlauthUserRedirectpid()
    {
        return $this->txNlauthUserRedirectpid;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getTxNlauthUserConfirmedat()
    {
        return $this->txNlauthUserConfirmedat;
    }

    /**
     * @param \DateTime $txNlauthUserConfirmedat
     * @api
     * @return $this
     */
    public function setTxNlauthUserConfirmedat(\DateTime $txNlauthUserConfirmedat)
    {
        $this->txNlauthUserConfirmedat = $txNlauthUserConfirmedat;
        return $this;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getTxNlauthUserApprovedat()
    {
        return $this->txNlauthUserApprovedat;
    }

    /**
     * @param \DateTime $txNlauthUserApprovedat
     * @api
     * @return $this
     */
    public function setTxNlauthUserApprovedat(\DateTime $txNlauthUserApprovedat)
    {
        $this->txNlauthUserApprovedat = $txNlauthUserApprovedat;
        return $this;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getTxNlauthUserDeclinedat()
    {
        return $this->txNlauthUserDeclinedat;
    }

    /**
     * @param \DateTime $txNlauthUserDeclinedat
     * @api
     * @return $this
     */
    public function setTxNlauthUserDeclinedat(\DateTime $txNlauthUserDeclinedat)
    {
        $this->txNlauthUserDeclinedat = $txNlauthUserDeclinedat;
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