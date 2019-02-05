<?php

namespace NL\NlAuth\Controller;


use NL\NlAuth\Type\AuthSignalType;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use NL\NlAuth\Type\LoginType;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * Class AuthController
 * @package NL\NlAuth\Controller
 */
class AuthController extends AbstractController
{
    /**
     * @inheritdoc
     */
    protected function defaultSettings()
    {
        return [
            'login' => [
                'page' => $this->getTypoScriptFrontendController()->id,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        $frameworkConfiguration = $this->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $view->assign('storagePid', $frameworkConfiguration['persistence']['storagePid']);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function showLoginFormAction()
    {
        /* @var $formData array */
        $formData = $this->request->getArgument('formData');

        if (isset($formData['logintype'])) {
            switch ($formData['logintype']) {
                case LoginType::LOGIN:
                    if ($this->authService->isUserLoggedIn()) {
                        $this->emitSignal(AuthSignalType::AFTER_SUCCESSFUL_LOGIN);

                        $this->addLocalizedFlashMessage(
                            'tx_nlauth_user.flash.login_success_message',
                            [$this->authService->getUser()->getUsername()],
                            FlashMessage::OK,
                            'tx_nlauth_user.flash.login_success_title'
                        );

                        if ($this->getSettingsValue('login.showLogoutFormAfterLogin')) {
                            $this->forward('showLogoutForm');
                        }
                    } else {
                        $results = $this->getControllerContext()->getRequest()->getOriginalRequestMappingResults();

                        /* @var $error Error */
                        $error = GeneralUtility::makeInstance(
                            Error::class,
                            LocalizationUtility::translate(
                                'validator.login.invalid',
                                $this->request->getControllerExtensionName()
                            ) ?: 'The email or password are invalid',
                            1515759132
                        );

                        $results->forProperty('pass')->addError($error);

                        $this->getControllerContext()->getRequest()->setOriginalRequestMappingResults($results);

                        $this->emitSignal(AuthSignalType::AFTER_FAILED_LOGIN);
                    }
                    break;
                case LoginType::LOGOUT:
                    $this->emitSignal(AuthSignalType::AFTER_LOGOUT);

                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.logout_success_message',
                        null,
                        FlashMessage::OK,
                        'tx_nlauth_user.flash.logout_success_title'
                    );
                    break;
            }
        } else {
            if ($this->authService->isUserLoggedIn()) {
                $this->emitSignal(AuthSignalType::ALREADY_LOGGED_IN);

                $this->forward('showLogoutForm');
            }
        }
    }


    /**
     *
     */
    public function showLogoutFormAction()
    {
        $user = $this->authService->getUser();

        $this->view->assignMultiple([
            'user' => $user,
        ]);
    }
}