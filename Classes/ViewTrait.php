<?php

namespace NL\NlAuth;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

trait ViewTrait
{
    /**
     * @param string $templateName
     * @param string $format
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    protected function getView($templateName, $format = 'html')
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setFormat($format);

        $request = $view->getRequest();
        $request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
        $request->setPluginName($this->frameworkConfiguration['pluginName']);

        $view->setLayoutRootPaths($this->getAbsoluteLayoutRootPath());
        $view->setPartialRootPaths($this->getAbsolutePartialRootPaths());
        $view->setTemplateRootPaths($this->getAbsoluteTemplateRootPaths());

        $view->setTemplate($templateName);

        return $view;
    }

    /**
     * Get absolute template root paths
     *
     * @return array
     */
    private function getAbsoluteTemplateRootPaths()
    {
        $templateRootPaths = [];
        if ($this->settings['templateRootPath']) {
            $templateRootPaths[] = trim($this->settings['templateRootPath']);
        }
        if (isset($this->frameworkConfiguration['view'])) {
            if (isset($this->frameworkConfiguration['view']['templateRootPath'])) {
                $templateRootPaths[] = $this->frameworkConfiguration['view']['templateRootPath'];
            }
            if (isset($this->frameworkConfiguration['view']['templateRootPaths'])) {
                $templateRootPaths = array_merge(
                    $templateRootPaths,
                    $this->frameworkConfiguration['view']['templateRootPaths']
                );
            }
        }
        if (empty($templateRootPaths)) {
            $templateRootPaths[] = ExtensionManagementUtility::extPath($this->frameworkConfiguration['extensionName']) . 'Resources/Private/Templates/';
        }

        $result = [];
        foreach ($templateRootPaths as $key => $value) {
            $value = GeneralUtility::getFileAbsFileName(trim($value));
            if (GeneralUtility::isAllowedAbsPath($value)) {
                $result[] = rtrim(trim($value), '/') . '/';
            }
        }
        return $result;
    }

    /**
     * Get absolute partial root paths
     *
     * @return array
     */
    private function getAbsolutePartialRootPaths()
    {
        $partialRootPaths = [];
        if ($this->settings['partialRootPath']) {
            $partialRootPaths[] = trim($this->settings['partialRootPath']);
        }
        if (isset($this->frameworkConfiguration['view'])) {
            if (isset($this->frameworkConfiguration['view']['partialRootPath'])) {
                $partialRootPaths[] = $this->frameworkConfiguration['view']['partialRootPath'];
            }
            if (isset($this->frameworkConfiguration['view']['partialRootPaths'])) {
                $partialRootPaths = array_merge(
                    $partialRootPaths,
                    $this->frameworkConfiguration['view']['partialRootPaths']
                );
            }
        }
        if (empty($partialRootPaths)) {
            $partialRootPaths[] = ExtensionManagementUtility::extPath($this->frameworkConfiguration['extensionName']) . 'Resources/Private/Partials/';
        }
        $result = [];
        foreach ($partialRootPaths as $key => $value) {
            $value = GeneralUtility::getFileAbsFileName(trim($value));
            if (GeneralUtility::isAllowedAbsPath($value)) {
                $result[] = rtrim(trim($value), '/') . '/';
            }
        }
        return $result;
    }

    /**
     * Get absolute layout root path
     *
     * @return array
     */
    private function getAbsoluteLayoutRootPath()
    {
        $layoutRootPaths = [];
        if ($this->settings['layoutRootPath']) {
            $layoutRootPaths = trim($this->settings['layoutRootPath']);
        }
        if (isset($this->frameworkConfiguration['view'])) {
            if (isset($this->frameworkConfiguration['view']['layoutRootPath'])) {
                $layoutRootPaths[] = $this->frameworkConfiguration['view']['layoutRootPath'];
            }
            if (isset($this->frameworkConfiguration['view']['layoutRootPaths'])) {
                $layoutRootPaths = array_merge(
                    $layoutRootPaths,
                    $this->frameworkConfiguration['view']['layoutRootPaths']
                );
            }
        }
        if (empty($layoutRootPaths)) {
            $layoutRootPaths[] = ExtensionManagementUtility::extPath($this->frameworkConfiguration['extensionName']) . 'Resources/Private/Layouts/';
        }
        $result = [];
        foreach ($layoutRootPaths as $key => $value) {
            $value = GeneralUtility::getFileAbsFileName(trim($value));
            if (GeneralUtility::isAllowedAbsPath($value)) {
                $result[] = rtrim(trim($value), '/') . '/';
            }
        }
        return $result;
    }
}