<?php

namespace NL\NlAuth\Domain\Service;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Type\UserTokenType;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class UserTokenService implements SingletonInterface
{
    /**
     * @var FrontendInterface
     */
    protected $tokenCache;

    /**
     * @param CacheManager $cacheManager
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->tokenCache = $cacheManager->getCache('nlauth_user_token');
    }

    /**
     * @param FrontendUser $user
     * @param string $type
     * @param array $additionalTokenValues
     * @param int $hashLength
     * @param int|null $tokenLifetime
     * @return string
     */
    public function setToken(FrontendUser $user, $type, $additionalTokenValues = [], $hashLength = 32, $tokenLifetime = null)
    {
        $hash = GeneralUtility::makeInstance(Random::class)->generateRandomHexString($hashLength);

        $uid = ObjectAccess::getPropertyPath($user, 'uid');
        $type = (string)UserTokenType::cast($type);

        $token = [
            'uid' => $uid,
            'type' => $type
        ];

        if (is_array($additionalTokenValues)) {
            ArrayUtility::mergeRecursiveWithOverrule($token, $additionalTokenValues);
        }

        $cacheTag = $this->getTokenTag($uid, $type);

        $this->tokenCache->flushByTag($cacheTag);
        $this->tokenCache->set($hash, $token, [$cacheTag], $tokenLifetime);

        return $hash;
    }

    /**
     * @param $hash
     * @param $type
     * @return bool|mixed
     */
    public function getToken($hash, $type)
    {
        $token = $this->tokenCache->get($hash);

        if ($token !== false) {
            if ($token['type'] === (string)UserTokenType::cast($type)) {
                return $token;
            }
        }

        return false;
    }

    /**
     * @param $hash
     * @return bool
     */
    public function removeToken($hash)
    {
        return $this->tokenCache->remove($hash);
    }

    /**
     * @param $lifetime
     * @return \DateTime
     * @throws \Exception
     */
    public function getTokenExpiryDateByLifetime($lifetime)
    {
        return new \DateTime(sprintf('now + %d seconds', $lifetime));
    }

    /**
     * @param int $uid
     * @param string $type
     * @return string
     */
    protected function getTokenTag($uid, $type)
    {
        return sprintf('%s_%s', $uid, UserTokenType::cast($type));
    }
}