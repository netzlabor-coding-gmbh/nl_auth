<?php

namespace NL\NlAuth\Domain\Validator;


use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class EqualValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'equalTo' => array(null, 'Another value to compare with', 'mixed', true),
        'strict' => array(false, 'TRUE for strict comparison (including type), FALSE otherwise', 'boolean'),
        'negate' => array(false, 'TRUE to validate against not equal, FALSE for equal', 'boolean'),
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
        $otherValue = $this->options['equalTo'];
        $valueIsValid = $this->options['strict'] ? $value === $otherValue : $value == $otherValue;
        $errorMessageTranslationKey = 'validator.equal.invalid';
        if ($this->options['negate']) {
            $valueIsValid = !$valueIsValid;
            $errorMessageTranslationKey = 'validator.equal.negate_invalid';
        }
        if (!$valueIsValid) {
            $this->addError(
                $this->translateErrorMessage(
                    $errorMessageTranslationKey,
                    'nl_auth'
                ),
                1514633369
            );
            return false;
        }
        return true;
    }
}