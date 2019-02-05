<?php

namespace NL\NlAuth\Domain\Service;


use NL\NlAuth\SettingsTrait;
use NL\NlAuth\ViewTrait;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class MailService implements SingletonInterface
{
    use SettingsTrait, ViewTrait;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    protected $settings;

    protected $frameworkConfiguration;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;

        $this->frameworkConfiguration = $this
            ->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $this->settings = $this
            ->configurationManager
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                $this->frameworkConfiguration['extensionName'],
                $this->frameworkConfiguration['pluginName']
            );
    }

    /**
     * @param FrontendUser $user
     * @param string $hash
     * @param string $hashUri
     * @param string $expiryDate
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendPasswordRecoveryMessage($user, $hash, $hashUri = '', $expiryDate = '')
    {
        return $this->sendMessage(
            $user->getEmail(),
            $this->getSettingsValue(
                'mail.passwordRecoverySubject',
                LocalizationUtility::translate(
                    'tx_nlauth_user.mail.password_recovery_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']]
                )
            ),
            'PasswordRecovery',
            [
                'user' => $user,
                'hash' => $hash,
                'hashUri' => $hashUri,
                'expiryDate' => $expiryDate,
            ]
        );
    }

    /**
     * @param FrontendUser $user
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendWelcomeMessage($user)
    {
        return $this->sendMessage(
            $user->getEmail(),
            $this->getSettingsValue(
                'mail.welcomeSubject',
                LocalizationUtility::translate(
                    'tx_nlauth_user.mail.welcome_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']]
                )
            ),
            'Welcome',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * @param FrontendUser $user
     * @param string $hash
     * @param string $hashUri
     * @param string $expiryDate
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendConfirmationMessage($user, $hash, $hashUri = '', $expiryDate = '')
    {
        return $this->sendMessage(
            $user->getEmail(),
            $this->getSettingsValue(
                'mail.confirmationSubject',
                LocalizationUtility::translate(
                    'tx_nlauth_user.mail.confirmation_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']]
                )
            ),
            'Confirmation',
            [
                'user' => $user,
                'hash' => $hash,
                'hashUri' => $hashUri,
                'expiryDate' => $expiryDate,
            ]
        );
    }

    /**
     * @param string $to
     * @param FrontendUser $user
     * @param array $hashUris
     * @param $declineHashUri
     * @param string $expiryDate
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendApprovementMessage($to, $user, $hashUris, $declineHashUri, $expiryDate = '')
    {
        return $this->sendMessage(
            $to,
            $this->getSettingsValue(
                'mail.approvementSubject',
                LocalizationUtility::translate(
                    'tx_nlauth_user.mail.approvement_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']]
                )
            ),
            'Approvement',
            [
                'user' => $user,
                'hashUris' => $hashUris,
                'declineHashUri' => $declineHashUri,
                'expiryDate' => $expiryDate,
            ]
        );
    }

    /**
     * @param FrontendUser $user
     * @param $isApproved
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendApproveStatusMessage($user, $isApproved)
    {
        return $this->sendMessage(
            $user->getEmail(),
            $this->getSettingsValue(
                'mail.approveStatusSubject',
                LocalizationUtility::translate(
                    'tx_nlauth_user.mail.approve_status_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']]
                )
            ),
            'ApproveStatus',
            ['isApproved' => $isApproved]
        );
    }

    /**
     * @param string $to
     * @param FrontendUser $user
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendUserDeletionMessage($to, $user)
    {
        return $this->sendMessage(
            $to,
            $this->getSettingsValue(
                'mail.deletionSubject',
                LocalizationUtility::translate(
                    'tx_nlauth_user.mail.deletion_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']]
                )
            ),
            'Deletion',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * @param $to
     * @param $subject
     * @param $view
     * @param array $values
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    protected function sendMessage($to, $subject, $view, $values = [])
    {
        $mergedValues = ['settings' => $this->settings];

        ArrayUtility::mergeRecursiveWithOverrule($mergedValues, $values);

        /* @var MailMessage */
        $mail = $this->objectManager->get(MailMessage::class);
        $mail
            ->setTo($to)
            ->setFrom($this->getFrom())
            ->setSubject($subject);

        $htmlView = $this->getView('Mail/' . $view, 'html');
        $htmlView->assignMultiple($mergedValues);
        $htmlBody = $htmlView->render();

        $mail->setBody($htmlBody, 'text/html');

        $plainView = $this->getView('Mail/' . $view, 'txt');
        $plainView->assignMultiple($mergedValues);
        $plainBody = $plainView->render();

        $mail->addPart($plainBody, 'text/plain');

        $mail->send();

        return $mail->isSent();
    }

    /**
     * @return array|mixed
     */
    protected function getFrom()
    {
        return $this->getSettingsValue('mail.fromName', MailUtility::getSystemFromName()) ?
            [
                $this->getSettingsValue('mail.fromEmail', MailUtility::getSystemFromAddress()) =>
                $this->getSettingsValue('mail.fromName', MailUtility::getSystemFromName())
            ] :
            $this->getSettingsValue('mail.fromEmail', MailUtility::getSystemFromAddress());
    }
}