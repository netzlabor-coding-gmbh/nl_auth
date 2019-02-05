<?php

namespace NL\NlAuth\Controller;

use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use NL\NlAuth\Domain\Service\AuthService;
use NL\NlAuth\SettingsTrait;
use NL\NlAuth\Utility\ErrorUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 * @package NL\NlAuth\Controller
 */
abstract class AbstractController extends ActionController
{
    use SettingsTrait;

    /**
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'json' => JsonView::class,
    ];

    /**
     * @param Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * @param FrontendUserRepository $repository
     */
    public function injectFrontedUserRepository(FrontendUserRepository $repository)
    {
        $this->frontendUserRepository = $repository;
    }

    /**
     * @param AuthService $service
     */
    public function injectAuthService(AuthService $service)
    {
        $this->authService = $service;
    }

    /**
     * Returns default settings values
     *
     * @return array
     */
    protected function defaultSettings()
    {
        return [];
    }

    /**
     * @inheritdoc
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        $settings = $this->defaultSettings();
        
        if(!is_array($this->settings)) {
            $this->settings = [];
        }

        ArrayUtility::mergeRecursiveWithOverrule($settings, $this->settings, true, false);
        $this->settings = $settings;

        $formData = GeneralUtility::_GET();
        ArrayUtility::mergeRecursiveWithOverrule($formData, GeneralUtility::_POST());
        $this->request->setArgument('formData', $formData);
    }

    /**
     * Flash messages helper that provides message localization
     *
     * @param string $translationKey
     * @param array $translationArguments
     * @param int $severity
     * @param string $messageTitle
     */
    protected function addLocalizedFlashMessage($translationKey, array $translationArguments = null, $severity = FlashMessage::OK, $messageTitle = '')
    {
        $this->addFlashMessage(
            LocalizationUtility::translate(
                $translationKey,
                $this->request->getControllerExtensionName(),
                $translationArguments
            ),
            ($messageTitle != '' ? LocalizationUtility::translate($messageTitle, $this->request->getControllerExtensionName(), $translationArguments) : ''),
            $severity
        );
    }

    /**
     * @inheritdoc
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        $view->assign('formData', $this->request->getArgument('formData'));
    }

    /**
     * @inheritdoc
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * @return false|string
     */
    protected function errorAction()
    {
        if ($this->request->getFormat() === "json") {
            $this->clearCacheOnError();
            $this->response->setStatus(422);

            return json_encode([
                "message" => $this->getFlattenedValidationErrorMessage(),
                "errors" => ErrorUtility::flattenedErrorsToArray($this->arguments->validate()->getFlattenedErrors()),
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $result = parent::errorAction();

            if ($this->request->getReferringRequest() === null) {
                call_user_func_array(
                    [$this, $this->getSettingsValue('defaultReferrer.method', 'forward')],
                    $this->getSettingsValue('defaultReferrer.arguments', [
                        'showLoginForm',
                        'Auth'
                    ])
                );
            }

            return $result;
        }
    }

    /**
     * @param $signalName
     * @param array $additionalProperties
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function emitSignal($signalName, $additionalProperties = [])
    {
        $properties = [$this, $this->settings];

        if (!empty($additionalProperties) && is_array($additionalProperties)) {
            array_push($properties, $additionalProperties);
        }

        $this->signalSlotDispatcher->dispatch(
            get_called_class(),
            $signalName,
            $properties
        );
    }
}