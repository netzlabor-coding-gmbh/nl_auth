<?php

namespace NL\NlAuth\Controller;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Domain\Model\FrontendUserGroup;
use NL\NlAuth\Domain\Repository\FrontendUserGroupRepository;
use NL\NlAuth\Domain\Service\MailService;
use NL\NlAuth\Domain\Service\UserTokenService;
use NL\NlAuth\Domain\Validator\UserTokenValidator;
use NL\NlAuth\Domain\Validator\ValidFrontendUserGroupValidator;
use NL\NlAuth\Type\RegistrationSignalType;
use NL\NlAuth\Type\UserTokenType;
use NL\NlAuth\Utility\PasswordUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

class RegistrationController extends AbstractController
{
    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var UserTokenService
     */
    protected $userTokenService;

    /**
     * @var FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * @param FrontendUserGroupRepository $frontendUserGroupRepository
     */
    public function injectFrontendUserGroupRepository(FrontendUserGroupRepository $frontendUserGroupRepository)
    {
        $this->frontendUserGroupRepository = $frontendUserGroupRepository;
    }

    /**
     * @param MailService $mailService
     */
    public function injectMailService(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
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
     * @inheritdoc
     */
    protected function defaultSettings()
    {
        return [
            'registration' => [
                'page' => $this->getTypoScriptFrontendController()->id,
                'confirmation' => [
                    'loginOnConfirm' => false,
                    'tokenLifetime' => 86400, // 1 day
                ],
                'approvement' => [
                    'tokenLifetime' => null,
                ],
            ]
        ];
    }

    /**
     * @param FrontendUser|null $user
     * @return string
     */
    public function showRegistrationFormAction(FrontendUser $user = null)
    {
        $this->view->assign('user', $user);
    }

    /**
     * @param FrontendUser $user
     * @validate $user \NL\NlAuth\Domain\Validator\AbstractEntityValidator(settingsPath='validation.registration.register.user')
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException
     * @throws \Exception
     */
    public function registerAction(FrontendUser $user)
    {

        if ($this->getSettingsValue('registration.overrideUserGroup')) {
            $user->overrideUsergroupsByUids(
                GeneralUtility::trimExplode(
                    ',',
                    $this->getSettingsValue('registration.overrideUserGroup'),
                    true
                )
            );
        }

        if ($this->getSettingsValue('registration.takeEmailAsUsername')) {
            $user->takeEmailAsUsername();
        }

        $user->setPassword(PasswordUtility::hashPassword($user->getPassword()));
        $user->setDisable(
            $this->getSettingsValue('registration.confirmation.enable') ||
            $this->getSettingsValue('registration.approvement.enable')
        );

        $this->frontendUserRepository->add($user);
        $this->persistenceManager->persistAll();

        if ($this->getSettingsValue('registration.notifications.welcome')) {
            $this->mailService->sendWelcomeMessage($user);
        }

        if ($this->getSettingsValue('registration.confirmation.enable')) {
            $this->processConfirmation($user);
        }

        if ($this->getSettingsValue('registration.approvement.enable')) {
            $this->processApprovement($user);
        }

        $this->addLocalizedFlashMessage(
            'tx_nlauth_user.flash.register_successful',
            null,
            FlashMessage::OK
        );

        $this->emitSignal(RegistrationSignalType::AFTER_SUCCESSFUL_REGISTRATION, ['user' => $user]);

        if (!$this->getSettingsValue('registration.redirectDisable') &&
            $this->getSettingsValue('registration.redirectPageRegistration')) {
            $this->redirectToUri(
                $this->buildUri($this->getSettingsValue('registration.redirectPageRegistration'))
            );
        }

        $this->redirect('showRegistrationForm');
    }

    /**
     * @param string $hash
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \Exception
     */
    public function confirmAction($hash)
    {
        $userTokenValidator = $this->validatorResolver->createValidator(UserTokenValidator::class, [
            'tokenType' => UserTokenType::CONFIRMATION,
            'hmacBy' => 'email',
            'findMethod' => 'findDisabledByUid',
        ]);

        $validationResults = $userTokenValidator->validate($hash);

        if ($validationResults->hasErrors()) {
            switch ($validationResults->getFirstError()->getCode()) {
                case 1515975493:
                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.confirmation_expired',
                        null,
                        FlashMessage::ERROR
                    );
                    break;
                case 1515975644:
                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.confirmation_invalid',
                        null,
                        FlashMessage::ERROR
                    );
                    break;
            };

            $this->emitSignal(RegistrationSignalType::AFTER_FAILED_CONFIRMATION);
        } else {
            $token = $this->userTokenService->getToken($hash, UserTokenType::CONFIRMATION);
            /* @var FrontendUser $user */
            $user = $this->frontendUserRepository->findDisabledByUid($token['uid']);

            $user->setConfirmed(
                $this->getSettingsValue('registration.approvement.enable')
            );

            $this->frontendUserRepository->update($user);
            $this->persistenceManager->persistAll();

            $this->userTokenService->removeToken($hash);

            if ($this->getSettingsValue('registration.confirmation.loginOnSuccess')) {
                $this->authService->login($user);

                $this->addLocalizedFlashMessage(
                    'tx_nlauth_user.flash.confirmation_loggedin',
                    null,
                    FlashMessage::OK
                );
            } else {
                $this->addLocalizedFlashMessage(
                    'tx_nlauth_user.flash.confirmation_completed',
                    null,
                    FlashMessage::OK
                );
            }

            $this->emitSignal(
                RegistrationSignalType::AFTER_SUCCESSFUL_CONFIRMATION,
                ['user' => $user]
            );
        }

        if (!$this->getSettingsValue('registration.redirectDisable') &&
            $this->getSettingsValue('registration.redirectPageConfirmation')) {
            $this->redirectToUri(
                $this->buildUri($this->getSettingsValue('registration.redirectPageConfirmation'))
            );
        }

        $this->redirect('showRegistrationForm');
    }

    /**
     * TODO: Resend confirmation link
     */
    public function resendAction()
    {

    }

    /**
     * @param string $hash
     * @param int $uid
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws \Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function approveAction($hash, $uid = null)
    {
        $findMethod = $this->getSettingsValue('registration.approvement.multiple')
            ? 'findWithDisabledByUid' : 'findDisabledByUid';

        $userTokenValidator = $this->validatorResolver->createValidator(UserTokenValidator::class, [
            'tokenType' => UserTokenType::APPROVEMENT,
            'hmacBy' => 'email',
            'findMethod' => $findMethod,
        ]);

        $validationResults = $userTokenValidator->validate($hash);

        if ($uid) {
            $userGroupValidator = $this->validatorResolver->createValidator(ValidFrontendUserGroupValidator::class, [
                'property' => 'uid',
            ]);

            $validationResults->merge(
                $userGroupValidator->validate($uid)
            );
        }

        if ($validationResults->hasErrors()) {
            switch ($validationResults->getFirstError()->getCode()) {
                case 1515975493:
                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.approvement_expired',
                        null,
                        FlashMessage::ERROR
                    );
                    break;
                case 1515975644:
                case 1516021046:
                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.approvement_invalid',
                        null,
                        FlashMessage::ERROR
                    );
                    break;
            };

            $this->emitSignal(RegistrationSignalType::AFTER_FAILED_APPROVEMENT);
        } else {
            $token = $this->userTokenService->getToken($hash, UserTokenType::APPROVEMENT);
            /* @var FrontendUser $user */
            $user = $this->frontendUserRepository->$findMethod($token['uid']);

            $isFirst = !$user->gettxNlauthUserApprovedat();
            $decline = false;

            if ($uid !== null) {
                /* @var FrontendUserGroup $userGroup */
                $userGroup = $this->frontendUserGroupRepository->findByUid($uid);

                $user->addUsergroup($userGroup);

                if ($this->getSettingsValue('registration.approvement.declineGroup') ==
                    $userGroup->getUid()) {

                    $decline = true;

                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.approvement_declined',
                        [$user->getUsername(), $userGroup->getTitle()],
                        FlashMessage::OK
                    );
                } else {
                    $this->addLocalizedFlashMessage(
                        'tx_nlauth_user.flash.approvement_group_completed',
                        [$user->getUsername(), $userGroup->getTitle()],
                        FlashMessage::OK
                    );
                }
            } else {
                $this->addLocalizedFlashMessage(
                    'tx_nlauth_user.flash.approvement_completed',
                    [$user->getUsername()],
                    FlashMessage::OK
                );
            }

            if ($decline) {
                $user->setDeclined();
            } else {
                $user->setApproved(
                    $this->getSettingsValue('registration.confirmation.enable')
                );
            }

            $this->frontendUserRepository->update($user);
            $this->persistenceManager->persistAll();

            if (!$this->getSettingsValue('registration.approvement.multiple')) {
                $this->userTokenService->removeToken($hash);
            }

            if ($this->getSettingsValue('registration.notifications.approve') && $isFirst) {
                $this->mailService->sendApproveStatusMessage($user, !$decline);
            }

            $this->emitSignal(
                RegistrationSignalType::AFTER_SUCCESSFUL_APPROVEMENT,
                ['user' => $user]
            );

            $this->view->assign('user', $user);
        }
    }

    /**
     * @param FrontendUser $user
     * @return bool
     * @throws \Exception
     * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException
     */
    protected function processConfirmation(FrontendUser $user)
    {
        $tokenLifetime = $this->getSettingsValue('registration.confirmation.tokenLifetime');

        $hash = $this->userTokenService->setToken(
            $user,
            UserTokenType::CONFIRMATION,
            ['hmac' => $this->hashService->generateHmac($user->getEmail())],
            64,
            $tokenLifetime
        );

        $expiryDate = $this->userTokenService->getTokenExpiryDateByLifetime($tokenLifetime);
        $hashUri = $this->getAbsoluteUriFor('confirm', ['hash' => $hash]);

        return $this->mailService->sendConfirmationMessage($user, $hash, $hashUri, $expiryDate);
    }

    /**
     * @param FrontendUser $user
     * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException
     * @throws \Exception
     */
    protected function processApprovement(FrontendUser $user)
    {
        $tokenLifetime = $this->getSettingsValue('registration.approvement.tokenLifetime');
        $expiryDate = $this->userTokenService->getTokenExpiryDateByLifetime($tokenLifetime);
        $hashUris = [];

        $hash = $this->userTokenService->setToken(
            $user,
            UserTokenType::APPROVEMENT,
            ['hmac' => $this->hashService->generateHmac($user->getEmail())],
            64,
            $tokenLifetime
        );

        if ($this->getSettingsValue('registration.approvement.assignGroup')) {
            $availableGroups = $this->getSettingsValue('registration.approvement.availableGroups') ?
                $this->frontendUserGroupRepository->findByUids(
                    $this->getSettingsValue('registration.approvement.availableGroups')
                ) :
                $this->frontendUserGroupRepository->findAll();

            foreach ($availableGroups as $group) {
                if ($group->getUid() != $this->getSettingsValue('registration.approvement.declineGroup')) {
                    $hashUris[$group->getTitle()] = $this->getAbsoluteUriFor(
                        'approve',
                        ['hash' => $hash, 'uid' => $group->getUid()]
                    );
                }
            }
        } else {
            $hashUris['Approve'] = $this->getAbsoluteUriFor('approve', ['hash' => $hash]);
        }

        if ($this->getSettingsValue('registration.approvement.declineGroup')) {
            $hashUris[$this->getSettingsValue('registration.approvement.declineGroup')] =
                $this->getAbsoluteUriFor('approve', [
                    'hash' => $hash,
                    'uid' => $this->getSettingsValue('registration.approvement.declineGroup')
                ]);
        }

        $adminMailList = GeneralUtility::trimExplode(
            ',',
            $this->getSettingsValue('registration.approvement.adminMailList'),
            true
        );

        foreach ($adminMailList as $email) {
            $this->mailService->sendApprovementMessage($email, $user, $hashUris, $expiryDate);
        }
    }

    /**
     * @param null $actionName
     * @param array $controllerArguments
     * @return string
     * @throws \Exception
     */
    protected function getAbsoluteUriFor($actionName = null, $controllerArguments = [])
    {
        return $this->getControllerContext()->getUriBuilder()
            ->setTargetPageUid($this->getSettingsValue('registration.page'))
            ->setCreateAbsoluteUri(true)
            ->setNoCache(true)
            ->uriFor($actionName, $controllerArguments);
    }

    /**
     * @param int $pageUid
     * @return string
     */
    protected function buildUri($pageUid)
    {
        return $this
            ->getControllerContext()
            ->getUriBuilder()
            ->reset()
            ->setTargetPageUid($pageUid)
            ->buildFrontendUri();
    }
}
