<?php

namespace NL\NlAuth\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository as ExtbaseFrontendUserGroupRepository;

/**
 * Class FrontendUserGroupRepository
 * @package NL\NlAuth\Domain\Repository
 */
class FrontendUserGroupRepository extends ExtbaseFrontendUserGroupRepository
{
    /**
     * @param array $uids
     * @param string $delim
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByUids($uids, $delim = ',')
    {
        if (is_string($uids)) {
            $uids = GeneralUtility::trimExplode($delim, $uids, true);
        }

        $query = $this->createQuery();

        $query->matching(
            $query->in('uid', $uids)
        );

        return $query->execute();
    }
}