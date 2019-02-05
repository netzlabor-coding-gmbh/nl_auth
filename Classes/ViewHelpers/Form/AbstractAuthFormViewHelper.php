<?php

namespace NL\NlAuth\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

use NL\NlAuth\Type\LoginType;

/**
 * @property ControllerContext controllerContext
 */
class AbstractAuthFormViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'form';

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('pageUid', 'integer', 'Target Page Uid');

        $this->registerTagAttribute('enctype', 'string', 'MIME type');
        $this->registerTagAttribute('method', 'string', 'GET or POST');
        $this->registerTagAttribute('name', 'string', 'Form name');
        $this->registerUniversalTagAttributes();
    }

    /**
     * @return void
     */
    protected function setFormActionUri()
    {
        if ($this->hasArgument('actionUri')) {
            $formActionUri = $this->arguments['actionUri'];
        } else {
            $formActionUri = $this->renderingContext->getControllerContext()->getUriBuilder()
                ->reset()
                ->setTargetPageUid($this->arguments['pageUid'])
                ->setTargetPageType($this->arguments['pageType'])
                ->setNoCache($this->arguments['noCache'])
                ->setUseCacheHash(!$this->arguments['noCacheHash'])
                ->setSection($this->arguments['section'])
                ->setCreateAbsoluteUri($this->arguments['absolute'])
                ->setArguments((array) $this->arguments['additionalParams'])
                ->setAddQueryString($this->arguments['addQueryString'])
                ->setArgumentsToBeExcludedFromQueryString((array) $this->arguments['argumentsToBeExcludedFromQueryString'])
                ->setFormat($this->arguments['format'])
                ->build();
        }
        $this->tag->addAttribute('action', $formActionUri);
    }

    /**
     * @return void
     */
    protected function setFormMethod()
    {
        if (strtolower($this->arguments['method']) === 'get') {
            $this->tag->addAttribute('method', 'get');
        } else {
            $this->tag->addAttribute('method', 'post');
        }
    }

    /**
     * @param string $loginType Login type, one of \NL\NlAuth\Type\LoginType
     * @return string
     */
    protected function renderHiddenLoginTypeField($loginType)
    {
        $loginType = LoginType::cast($loginType);
        return LF . '<input type="hidden" name="logintype" value="' . $loginType . '" />' . LF;
    }
}