<?php

namespace NL\NlAuth\Domain\Validator;


use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class FrontendUserIsSetPropertyValidator extends AbstractValidator
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
        'findBy' => array('username', 'The property to find the frontend user', 'string'),
        'equalTo' => array(null, 'Value to compare with property', 'mixed'),
        'strict' => array(false, 'TRUE for strict comparison (including type), FALSE otherwise', 'boolean'),
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
        $findMethod = 'findBy' . ucfirst($this->options['findBy']);
        /* @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser */
        $user = $this->frontendUserRepository->$findMethod($value);

        $value = ObjectAccess::getPropertyPath($user, $this->options['property']);
        $isNotSet = $this->options['strict'] ? $value === $this->options['equalTo'] : $value == $this->options['equalTo'];

        if ($isNotSet) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.frontendUserIsSetProperty.invalid',
                    'nl_auth',
                    [$this->options['property']]
                ),
                1515966412
            );
            return false;
        }

        return true;
    }
}