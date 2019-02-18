<?php

namespace NL\NlAuth\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup as ExtbaseFrontendUserGroup;

/**
 * Class FrontendUserGroup
 * @package NL\NlAuth\Domain\Model
 */
class FrontendUserGroup extends ExtbaseFrontendUserGroup
{
    /**
     * @var int
     */
    protected $txNlauthUserRedirectpid;

    /**
     * @param int $txNlauthUserRedirectPid
     * @return FrontendUserGroup
     */
    public function setTxNlauthUserRedirectpid($txNlauthUserRedirectPid)
    {
        $this->txNlauthUserRedirectpid = $txNlauthUserRedirectPid;
        return $this;
    }

    /**
     * @return int
     */
    public function getTxNlauthUserRedirectpid()
    {
        return $this->txNlauthUserRedirectpid;
    }
}