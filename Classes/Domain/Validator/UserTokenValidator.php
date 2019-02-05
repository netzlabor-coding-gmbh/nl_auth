<?php

namespace NL\NlAuth\Domain\Validator;


use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use NL\NlAuth\Domain\Service\UserTokenService;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class UserTokenValidator extends AbstractValidator
{
    /**
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var UserTokenService
     */
    protected $userTokenService;

    /**
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * @param HashService $hashService
     */
    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @param UserTokenService $userTokenService
     */
    public function injectUserTokenService(UserTokenService $userTokenService)
    {
        $this->userTokenService = $userTokenService;
    }

    /**
     * @var array
     */

    protected $supportedOptions = array(
        'tokenType' => array('', 'User token type (defined in UserTokenType)', 'string', true),
        'hmacBy' => array(null, 'User property for hmac validation', 'string'),
        'findMethod' => array('findByIdentifier', 'Find method for frontend user', 'string')
    );

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isValid($value)
    {
        $token = $this->userTokenService->getToken($value, $this->options['tokenType']);

        if (!$token) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.userTokenValidator.expired',
                    'nl_auth'
                ),
                1515975493
            );
            return false;
        }

        $findMethod = $this->options['findMethod'];

        $user = $this->frontendUserRepository->$findMethod($token['uid']);

        if (!$user) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.userTokenValidator.invalid',
                    'nl_auth'
                ),
                1515975644
            );
            return false;
        }

        if ($this->options['hmacBy']) {
            $hmacProperty = ObjectAccess::getPropertyPath($user, $this->options['hmacBy']);

            if (!$this->hashService->validateHmac($hmacProperty, $token['hmac'])) {
                $this->addError(
                    $this->translateErrorMessage(
                        'validator.userTokenValidator.expired',
                        'nl_auth'
                    ),
                    1515975493
                );
                return false;
            }
        }

        return true;
    }
}