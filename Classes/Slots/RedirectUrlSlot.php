<?php

namespace NL\NlAuth\Slots;


use NL\NlAuth\Domain\Model\FrontendUser;
use NL\NlAuth\Domain\Model\FrontendUserGroup;
use NL\NlAuth\Domain\Repository\FrontendUserGroupRepository;
use NL\NlAuth\Domain\Repository\FrontendUserRepository;
use NL\NlAuth\Domain\Service\AuthService;
use NL\NlAuth\SettingsTrait;
use NL\NlAuth\Type\AuthSignalType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class RedirectUrlSlot
{
    use SettingsTrait;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    protected $frameworkConfiguration;

    /**
     * Settings of the create controller
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Request form data
     *
     * @var array
     */
    protected $formData = [];

    /**
     * @var ActionController
     */
    protected $emitterActionController;

    /**
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @var FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;

        $this->frameworkConfiguration = $this
            ->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
    }

    /**
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * @param FrontendUserGroupRepository $frontendUserGroupRepository
     */
    public function injectFrontendUserGroupRepository(FrontendUserGroupRepository $frontendUserGroupRepository)
    {
        $this->frontendUserGroupRepository = $frontendUserGroupRepository;
    }

    /**
     * @param AuthService $authService
     */
    public function injectAuthService(AuthService $authService)
    {
        $this->authService = $authService;
    }


    /**
     * Performs a redirect if possible
     *
     * @param ActionController $emitterActionController
     * @param array $settings
     * @param string $signalName
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function processRedirect(ActionController $emitterActionController, $settings, $signalName)
    {
        $this->settings = $settings;
        $this->emitterActionController = $emitterActionController;
        $this->formData = $this->emitterActionController
            ->getControllerContext()
            ->getRequest()
            ->getArgument('formData');

        list($emitterClassName, $signalName) = GeneralUtility::trimExplode(
            '::', $signalName,
            true
        );

        if (AuthSignalType::cast($signalName)) {
            if ($this->getSettingsValue('login.redirectMode')
                && !$this->getSettingsValue('login.redirectDisable')
                && !$this->getSettingsValue('login.showLogoutFormAfterLogin')) {

                $redirectModes = GeneralUtility::trimExplode(
                    ',',
                    $this->getSettingsValue('login.redirectMode'),
                    true
                );

                $redirectUrl = $this->getUrlByModes($redirectModes, $signalName);

                if ($redirectUrl) {
                    HttpUtility::redirect($redirectUrl);
                }
            }
        }
    }

    /**
     * @param $redirectModes
     * @param $signalName
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function getUrlByModes($redirectModes, $signalName)
    {
        $redirectUrls = [];

        foreach ($redirectModes as $mode) {
            switch ($signalName) {
                case AuthSignalType::AFTER_SUCCESSFUL_LOGIN:
                case AuthSignalType::ALREADY_LOGGED_IN:
                    switch ($mode) {
                        case 'groupLogin':
                            $redirectUrls[] = $this->getGroupLoginRedirectUrl();
                            break;
                        case 'userLogin':
                            $redirectUrls[] = $this->getUserLoginRedirectUrl();
                            break;
                        case 'login':
                            $redirectUrls[] = $this->getLoginRedirectUrl();
                            break;
                        case 'getpost':
                            $redirectUrls[] = $this->getGPRedirectUrl();
                            break;
                        case 'referer':
                            $redirectUrls[] = $this->getRefererRedirectUrl();
                            break;
                        case 'refererDomains':
                            $redirectUrls[] = $this->getRefererDomainsRedirectUrl();
                            break;
                    }
                    break;
                case AuthSignalType::AFTER_FAILED_LOGIN:
                    switch ($mode) {
                        case 'loginError':
                            $redirectUrls[] = $this->getLoginErrorRedirectUrl();
                            break;
                    }
                    break;
                case AuthSignalType::AFTER_LOGOUT:
                    switch ($mode) {
                        case 'logout':
                            $redirectUrls[] = $this->getLogoutRedirectUrl();
                            break;
                    }
                    break;
                default:
                    switch ($mode) {
                        case 'getpost':
                            $redirectUrls[] = $this->getGPRedirectUrl();
                            break;
                    }
            }
        }

        if (!empty($redirectUrls)) {
            $redirectUrls = array_filter($redirectUrls, 'strlen');

            return $this->getSettingsValue('login.redirectFirstMethod')
                ? array_shift($redirectUrls) : array_pop($redirectUrls);
        }

        return '';
    }

    /**
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function getGroupLoginRedirectUrl()
    {
        $userGroups = $this->authService->getUserGroups();

        if ($userGroups && $userGroups instanceof QueryResultInterface) {
            foreach ($userGroups as $group) {
                if ($group instanceof FrontendUserGroup && !empty($group->getTxNlauthUserRedirectpid())) {
                    return $this->buildUri((int)$group->getTxNlauthUserRedirectpid());
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getUserLoginRedirectUrl()
    {
        $user = $this->authService->getUser();

        if ($user instanceof FrontendUser && !empty($user->getTxNlauthUserRedirectpid())) {
            return $this->buildUri((int)$user->getTxNlauthUserRedirectpid());
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getLoginRedirectUrl()
    {
        if ($this->getSettingsValue('login.redirectPageLogin')) {
            return $this->buildUri((int)$this->getSettingsValue('login.redirectPageLogin'));
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getGPRedirectUrl()
    {
        $gpRedirectUrl = '';
        // May be set by anything
        if (!empty($this->formData['redirect_url'])) {
            $gpRedirectUrl = $this->formData['redirect_url'];
        }
        // May be set via config.typolinkLinkAccessRestrictedPages_addParams
        if (!empty($this->formData['return_url'])) {
            $gpRedirectUrl = $this->formData['return_url'];
        }

        return $gpRedirectUrl;
    }

    /**
     * @return string
     */
    protected function getRefererRedirectUrl()
    {
        if ($this->formData['redirectReferrer'] !== 'off' && !empty($this->formData['referer'])) {
            return preg_replace('/[&?]logintype=[a-z]+/', '', $this->formData['referer']);
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getRefererDomainsRedirectUrl()
    {
        if ($this->formData['redirectReferrer'] !== 'off' && $this->getSettingsValue('login.domains')) {
            $url = $this->formData['referer'];
            // Is referring url allowed to redirect?
            $match = [];
            if (preg_match('#^http://([[:alnum:]._-]+)/#', $url, $match)) {
                $redirectDomain = $match[1];
                $found = false;
                foreach (GeneralUtility::trimExplode(',', $this->getSettingsValue('login.domains'), true) as $d) {
                    if (preg_match('/(?:^|\\.)' . $d . '$/', $redirectDomain)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $url = '';
                }
            }

            if ($url) {
                return preg_replace('/[&?]logintype=[a-z]+/', '', $url);
            }
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getLoginErrorRedirectUrl()
    {
        if ($this->getSettingsValue('login.redirectPageLoginError')) {
            return $this->buildUri((int)$this->getSettingsValue('login.redirectPageLoginError'));
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getLogoutRedirectUrl()
    {
        if ($this->getSettingsValue('login.redirectPageLogout')) {
            return $this->buildUri((int)$this->getSettingsValue('login.redirectPageLogout'));
        }

        return '';
    }

    /**
     * @param int $pageUid
     * @return string
     */
    protected function buildUri($pageUid)
    {
        return $this->emitterActionController
            ->getControllerContext()
            ->getUriBuilder()
            ->reset()
            ->setTargetPageUid($pageUid)
            ->buildFrontendUri();
    }
}