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
    protected $txNlAuthUserRedirectpid;

    /**
     * @param int $txNlAuthUserRedirectPid
     * @return FrontendUserGroup
     */
    public function setTxNlAuthUserRedirectpid($txNlAuthUserRedirectPid)
    {
        $this->txNlAuthUserRedirectpid = $txNlAuthUserRedirectPid;
        return $this;
    }

    /**
     * @return int
     */
    public function getTxNlAuthUserRedirectpid()
    {
        return $this->txNlAuthUserRedirectpid;
    }
}