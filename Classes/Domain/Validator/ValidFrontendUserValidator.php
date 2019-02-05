<?php

namespace NL\NlAuth\Domain\Validator;


use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ValidFrontendUserValidator extends AbstractValidator
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
        'property' => array('', 'The property to use for frontend user lookup', 'string', true)
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
        $countMethod = 'countBy' . ucfirst($this->options['property']);
        $count = $this->frontendUserRepository->$countMethod($value);
        if ($count === 0) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.validFrontendUser.invalid',
                    'nl_auth',
                    [$value]
                ),
                1514633458
            );
            return false;
        }
        return true;
    }
}