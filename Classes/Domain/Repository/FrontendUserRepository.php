<?php

namespace NL\NlAuth\Domain\Repository;


use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository as ExtbaseFrontendUserRepository;

/**
 * Class FrontendUserRepository
 * @package NL\NlAuth\Domain\Repository
 *
 * @method mixed findByEmail(string $email)
 * @method mixed findByUsername(string $username)
 * @method countByEmail($usernameOrEmail)
 * @method countByUsername($usernameOrEmail)
 */
class FrontendUserRepository extends ExtbaseFrontendUserRepository
{
    /**
     * Finds a Frontend user by username or email
     *
     * @param $usernameOrEmail
     * @return mixed
     */
    public function findOneByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findByEmail($usernameOrEmail)->getFirst();
        }

        return $this->findByUsername($usernameOrEmail)->getFirst();
    }

    /**
     * @param $usernameOrEmail
     * @return mixed
     */
    public function findByUsernameOrEmail($usernameOrEmail)
    {
        return $this->findOneByUsernameOrEmail($usernameOrEmail);
    }

    /**
     * @param $usernameOrEmail
     * @return mixed
     */
    public function countByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->countByEmail($usernameOrEmail);
        }

        return $this->countByUsername($usernameOrEmail);
    }

    /**
     * @param $uid
     * @return object
     */
    public function findDisabledByUid($uid)
    {
        $this->ignoreEnableFields(['disabled']);

        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd([
                $query->equals('uid', $uid),
                $query->equals('disable', true)
            ])
        );

        return $query->execute()->getFirst();
    }

    /**
     * @param $uid
     * @return object
     */
    public function findWithDisabledByUid($uid)
    {
        $this->ignoreEnableFields(['disabled']);

        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd([
                $query->equals('uid', $uid),
            ])
        );

        return $query->execute()->getFirst();
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function ignoreEnableFields($fields = null)
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true);

        if (isset($fields) && is_array($fields)) {
            $querySettings->setEnableFieldsToBeIgnored($fields);
        }

        $this->setDefaultQuerySettings($querySettings);

        return $this;
    }


    /**
     * @param $field
     * @param $value
     * @param bool $respectStoragePage
     * @return int
     */
    public function countByField($field, $value, $respectStoragePage = true)
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage($respectStoragePage);
        $querySettings->setIgnoreEnableFields(true);

        return $query->matching($query->equals($field, $value))
            ->setLimit(1)
            ->execute()
            ->count();
    }


    /**
     * @param $field
     * @param $value
     * @return int
     */
    public function countByFieldGlobal($field, $value)
    {
        return $this->countByField($field, $value, false);
    }
}