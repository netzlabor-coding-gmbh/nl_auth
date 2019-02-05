<?php

namespace NL\NlAuth\ViewHelpers;


use NL\NlAuth\Type\LoginType;
use NL\NlAuth\ViewHelpers\Form\AbstractAuthFormViewHelper;

class LogoutFormViewHelper extends AbstractAuthFormViewHelper
{
    /**
     * @param int $pageType
     * @param bool $noCache
     * @param bool $noCacheHash
     * @param string $section
     * @param string $format
     * @param array $additionalParams
     * @param bool $absolute
     * @param bool $addQueryString
     * @param array $argumentsToBeExcludedFromQueryString
     * @param null $actionUri
     * @return string
     */
    public function render($pageType = 0, $noCache = false, $noCacheHash = false, $section = '', $format = '', array $additionalParams = array(), $absolute = false, $addQueryString = false, array $argumentsToBeExcludedFromQueryString = array(), $actionUri = null)
    {
        $this->setFormActionUri();
        $this->setFormMethod();
        $content = $this->renderHiddenLoginTypeField(LoginType::LOGOUT);
        $content .= $this->renderChildren();
        $this->tag->setContent($content);
        return $this->tag->render();
    }
}