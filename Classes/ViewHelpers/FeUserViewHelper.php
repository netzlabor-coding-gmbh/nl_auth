<?php

namespace NL\NlAuth\ViewHelpers;


use NL\NlAuth\Domain\Service\AuthService;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class FeUserViewHelper extends AbstractViewHelper
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * @param AuthService $service
     */
    public function injectAuthService(AuthService $service)
    {
        $this->authService = $service;
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('property', 'string', 'Key of user property to retrieve');
    }

    /**
     *
     */
    public function render()
    {
        $feUser = $this->authService->getUser();

        $value = $this->arguments['property'] ?
            ObjectAccess::getPropertyPath($feUser, $this->arguments['property']) : $feUser;

        return $value;
    }
}