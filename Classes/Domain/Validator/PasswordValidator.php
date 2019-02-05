<?php

namespace NL\NlAuth\Domain\Validator;


use NL\NlAuth\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

class PasswordValidator extends AbstractValidator
{
    /* @var ValidatorResolver */
    protected $validatorResolver;

    /**
     * @param ValidatorResolver $validatorResolver
     */
    public function injectValidatorResolver(ValidatorResolver $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param FrontendUser $user
     * @return bool
     */
    protected function isValid($user)
    {
        $isValid = true;

        $equalValidator = $this->validatorResolver->createValidator(EqualValidator::class, [
            'equalTo' => ObjectAccess::getPropertyPath($user, 'password'),
        ]);

        $validationResults = $equalValidator->validate($user->getPasswordRepeat());

        if ($validationResults->hasErrors()) {
            $this->result->forProperty('passwordRepeat')->merge($validationResults);
            $isValid = false;
        }

        return $isValid;
    }
}