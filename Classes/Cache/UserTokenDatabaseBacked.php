<?php

namespace NL\NlAuth\Cache;


use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;

class UserTokenDatabaseBacked extends Typo3DatabaseBackend
{
    /**
     * Removes all cache entries of this cache.
     */
    public function flush()
    {
        //disable flush user token cache
    }
}