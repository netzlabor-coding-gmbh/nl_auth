<?php

namespace NL\NlAuth\Utility;


use TYPO3\CMS\Extbase\Error\Error;

class ErrorUtility
{
    /**
     * @param Error[] $flattenedErrors
     * @return array
     */
    public static function flattenedErrorsToArray($flattenedErrors)
    {
        $result = [];

        $convertErrors = function ($error, &$result) use (&$convertErrors) {
            if ($error instanceof Error) {
                $result = [
                    'message' => $error->getMessage(),
                    'arguments' => $error->getArguments(),
                    'code' => $error->getCode(),
                    'title' => $error->getTitle(),
                ];
            } elseif (is_array($error)) {
                foreach ($error as $key => $value) {
                    $convertErrors($value, $result[$key]);
                }
            }
        };

        $convertErrors($flattenedErrors, $result);

        return $result;
    }
}