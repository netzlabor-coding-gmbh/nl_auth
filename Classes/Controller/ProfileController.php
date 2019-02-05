<?php

namespace NL\NlAuth\Controller;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Domain\Service\MailService;
use NL\NlAuth\Type\LoginType;
use NL\NlAuth\Utility\PasswordUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProfileController extends AbstractController
{
    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @param MailService $mailService
     */
    public function injectMailService(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     *
     */
    public function editAction()
    {
        $this->view->assign('user', $this->authService->getUser());
    }

    /**
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function initializeUpdateAction()
    {
        $args = $this->request->getArguments();

        /**
         * @var $user array
         */
        $user = $this->request->getArgument('user');

        if ((int)$user['__identity'] !== $this->authService->getUser()->getUid()) {
            $this->forward('edit');
        }

        if (empty($user['password']) && empty($user['passwordRepeat'])) {
            unset($args['user']['password']);
            unset($args['user']['passwordRepeat']);
        }

        $this->request->setArguments($args);
    }

    /**
     * @param FrontendUser $user
     * @validate $user \NL\NlAuth\Domain\Validator\AbstractEntityValidator(settingsPath='validation.profile.update.user')
     * @throws \Exception
     */
    public function updateAction(FrontendUser $user)
    {
        if ($this->getSettingsValue('profile.takeEmailAsUsername')) {
            $user->takeEmailAsUsername();
        }

        if ($user->_isDirty('password')) {
            $user->setPassword(PasswordUtility::hashPassword($user->getPassword()));
        }

        $this->frontendUserRepository->update($user);

        $this->addLocalizedFlashMessage(
            'tx_nlauth_user.flash.update_profile_success',
            null,
            FlashMessage::OK
        );

        $this->redirect('edit');
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function deleteAction()
    {
        if (!$this->getSettingsValue('profile.deletion.enable')) {
            $this->forward('edit');
        }

        /* @var \NL\NlAuth\Domain\Model\FrontendUser $user */
        $user = $this->authService->getUser();

        if ($this->getSettingsValue('profile.deletion.hard')) {
            /* @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');

            $queryBuilder
                ->delete('fe_users')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user->getUid(), \PDO::PARAM_INT))
                )
                ->execute();
        } else {
            $this->frontendUserRepository->remove($user);
        }

        if ($this->getSettingsValue('profile.deletion.notifyAdmin')) {
            $adminMailList = GeneralUtility::trimExplode(
                ',',
                $this->getSettingsValue('profile.deletion.adminMailList'),
                true
            );

            foreach ($adminMailList as $email) {
                $this->mailService->sendUserDeletionMessage($email, $user);
            }
        }

        $this->addLocalizedFlashMessage(
            'tx_nlauth_user.flash.delete_profile_success',
            null,
            FlashMessage::OK
        );

        $this->redirectToUri(
            $this
                ->uriBuilder
                ->reset()
                ->setTargetPageUid($this->getSettingsValue('login.page'))
                ->setArguments(['logintype' => LoginType::LOGOUT])
                ->uriFor('showLoginForm', null, 'Auth')
        );
    }
}
