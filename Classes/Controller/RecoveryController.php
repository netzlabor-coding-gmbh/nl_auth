<?php

namespace NL\NlAuth\Controller;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Domain\Service\MailService;
use NL\NlAuth\Domain\Service\UserTokenService;
use NL\NlAuth\Domain\Validator\UserTokenValidator;
use NL\NlAuth\Type\UserTokenType;
use NL\NlAuth\Utility\PasswordUtility;
use NL\NlAuth\Domain\Validator\EqualValidator;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractCompositeValidator;

class RecoveryController extends AbstractController
{
    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var UserTokenService
     */
    protected $userTokenService;

    /**
     * @param HashService $hashService
     */
    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @param MailService $mailService
     */
    public function injectMailService(MailService $mailService)
    {
        $this->mailService = $mailService;
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
            'passwordRecovery' => [
                'page' => $this->getTypoScriptFrontendController()->id,
                'loginOnSuccess' => false,
                'token' => [
                    'lifetime' => 86400, // 1 day
                ],
            ]
        ];
    }

    /**
     * Shows password reset request form
     *
     * @param bool $start
     * @return string
     */
    public function showPasswordResetRequestFormAction($start = false)
    {
        if ($start) {
            $this->addLocalizedFlashMessage(
                'tx_nlauth_user.flash.password_request_reset',
                null,
                FlashMessage::INFO
            );
        }
    }

    /**
     * @param string $hash
     * @throws StopActionException
     */
    public function showPasswordResetFormAction($hash)
    {
        $this->processTokenValidation($hash);

        $this->view->assign('hash', $hash);
    }

    /**
     * Sends password reset link if user exists
     *
     * @param string $usernameOrEmail
     * @return void
     * @throws InvalidArgumentForHashGenerationException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \Exception
     * @validate $usernameOrEmail NotEmpty
     * @validate $usernameOrEmail \NL\NlAuth\Domain\Validator\ValidFrontendUserValidator(property='usernameOrEmail')
     * @validate $usernameOrEmail \NL\NlAuth\Domain\Validator\FrontendUserIsSetProperty(property='email', findBy='UsernameOrEmail')
     * @validate $usernameOrEmail \NL\NlAuth\Domain\Validator\FrontendUserIsSetProperty(property='password', findBy='UsernameOrEmail')
     */
    public function requestPasswordResetAction($usernameOrEmail)
    {
        $user = $this->frontendUserRepository->findOneByUsernameOrEmail($usernameOrEmail);

        $password = ObjectAccess::getPropertyPath($user, 'password');

        $tokenLifetime = $this->getSettingsValue('passwordRecovery.token.lifetime');

        $hash = $this->userTokenService->setToken(
            $user,
            UserTokenType::PASSWORD_RECOVERY,
            ['hmac' => $this->hashService->generateHmac($password)],
            64,
            $tokenLifetime
        );

        $expiryDate = $this->userTokenService->getTokenExpiryDateByLifetime($tokenLifetime);

        $hashUri = $this->getControllerContext()->getUriBuilder()
            ->setTargetPageUid($this->getSettingsValue('passwordRecovery.page'))
            ->setCreateAbsoluteUri(true)
            ->setNoCache(true)
            ->uriFor('showPasswordResetForm', [
                'hash' => $hash,
            ]);

        $isSent = $this->mailService->sendPasswordRecoveryMessage($user, $hash, $hashUri, $expiryDate);

        $message = LocalizationUtility::translate(
            $isSent ? 'tx_nlauth_user.flash.password_reset_started' : 'tx_nlauth_user.flash.password_reset_started_error',
            $this->request->getControllerExtensionName()
        );

        $this->addFlashMessage(
            $message,
            null,
            $isSent ? FlashMessage::OK : FlashMessage::ERROR
        );

        $this->redirectToUri($this->buildUri(
            $this->getSettingsValue('passwordRecovery.page')
        ));
    }

    /**
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function initializePasswordResetAction()
    {
        /* @var AbstractCompositeValidator $passwordRepeatArgumentValidator */
        $passwordRepeatArgumentValidator = $this->arguments->getArgument('passwordRepeat')->getValidator();
        $passwordsEqualValidator = $this->validatorResolver->createValidator(EqualValidator::class, [
            'equalTo' => $this->request->getArgument('password'),
        ]);
        $passwordRepeatArgumentValidator->addValidator($passwordsEqualValidator);
    }

    /**
     * @param string $hash
     * @param string $password
     * @param string $passwordRepeat
     * @validate $password NotEmpty, String, StringLength(minimum=8, maximum=20)
     * @validate $passwordRepeat NotEmpty, String, StringLength(minimum=8, maximum=20)
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function passwordResetAction($hash, $password, $passwordRepeat)
    {
        $this->processTokenValidation($hash);

        $token = $this->userTokenService->getToken($hash, UserTokenType::PASSWORD_RECOVERY);

        /**
         * @var FrontendUser $user
         */
        $user = $this->frontendUserRepository->findByIdentifier($token['uid']);

        $user->setPassword(PasswordUtility::hashPassword($password));
        $this->frontendUserRepository->update($user);
        $this->userTokenService->removeToken($hash);

        $transKey = 'password_reset_completed';
        if ($this->getSettingsValue('passwordRecovery.loginOnSuccess')) {
            $this->authService->login($user);

            $transKey = 'password_reset_loggedin';
        }

        $message = LocalizationUtility::translate(
            'tx_nlauth_user.flash.' . $transKey,
            $this->request->getControllerExtensionName()
        );

        $uri = $this->buildUri(
            $this->getSettingsValue('passwordRecovery.redirectDisable') ?
                $this->getSettingsValue('passwordRecovery.page') :
                $this->getSettingsValue('passwordRecovery.redirectPageReset', $this->getSettingsValue('passwordRecovery.page'))
        );

        $this->addFlashMessage(
            $message,
            null,
            FlashMessage::OK
        );

        $this->redirectToUri($uri);

    }

    /**
     * @param $hash
     * @throws StopActionException
     */
    public function processTokenValidation($hash)
    {
        $userTokenValidator = $this->validatorResolver->createValidator(UserTokenValidator::class, [
            'tokenType' => UserTokenType::PASSWORD_RECOVERY,
            'hmacBy' => 'password'
        ]);

        $validationResults = $userTokenValidator->validate($hash);

        if ($validationResults->hasErrors()) {
            $results = $this->getControllerContext()->getRequest()->getOriginalRequestMappingResults();

            $results->forProperty('usernameOrEmail')->merge($validationResults);

            $this->getControllerContext()->getRequest()->setOriginalRequestMappingResults($results);

            $this->forward(str_replace("Action", "", $this->errorMethodName));
        }
    }

    /**
     * @param int $pageUid
     * @param bool $absolute
     * @return string
     */
    protected function buildUri($pageUid, $absolute = false)
    {
        return $this
            ->getControllerContext()
            ->getUriBuilder()
            ->reset()
            ->setCreateAbsoluteUri($absolute)
            ->setTargetPageUid($pageUid)
            ->buildFrontendUri();
    }
}