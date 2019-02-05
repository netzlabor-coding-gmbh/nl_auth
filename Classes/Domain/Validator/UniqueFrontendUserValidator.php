<?php

namespace NL\NlAuth\Domain\Validator;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class UniqueFrontendUserValidator extends AbstractValidator
{
    /**
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * @var array
     */

    protected $supportedOptions = array(
        'property' => array('', 'The property to use for frontend user lookup', 'string', true),
        'global' => array(true, 'Check unique globally'),
        'dirty' => array(false, 'Validate only if property is dirty'),
    );

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param FrontendUser $user
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException
     */
    protected function isValid($user)
    {
        $property = $this->options['property'];

        if ($this->options['dirty'] && !$user->_isDirty($property)) {
            return true;
        }

        $value = ObjectAccess::getPropertyPath($user, $property);

        $count = $this->frontendUserRepository->countByField($property, $value, !$this->options['global']);

        if ($count !== 0) {

            /* @var $error Error */
            $error = GeneralUtility::makeInstance(
                Error::class,
                $this->translateErrorMessage(
                    'validator.uniqueFrontendUser.invalid',
                    'nl_auth',
                    [$value]
                ),
                1516001603
            );

            $this->result->forProperty($property)->addError($error);
            return false;
        }
        return true;
    }
}