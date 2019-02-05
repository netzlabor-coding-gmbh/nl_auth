<?php

namespace NL\NlAuth\Domain\Validator;


use NL\NlAuth\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ValidFrontendUserGroupValidator extends AbstractValidator
{
    /**
     * @var FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * @param FrontendUserGroupRepository $frontendUserGroupRepository
     */
    public function injectFrontendUserRepository(FrontendUserGroupRepository $frontendUserGroupRepository)
    {
        $this->frontendUserGroupRepository = $frontendUserGroupRepository;
    }

    /**
     * @var array
     */

    protected $supportedOptions = array(
        'property' => array('', 'The property to use for frontend user group lookup', 'string', true)
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
        $count = $this->frontendUserGroupRepository->$countMethod($value);
        if ($count === 0) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.validFrontendUserGroup.invalid',
                    'nl_auth',
                    [$value]
                ),
                1516021046
            );
            return false;
        }
        return true;
    }
}