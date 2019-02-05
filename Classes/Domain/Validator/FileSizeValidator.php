<?php

namespace NL\NlAuth\Domain\Validator;


use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class FileSizeValidator extends AbstractValidator
{
    protected $supportedOptions = array(
        'minimum' => array(0, 'File size min in bytes', 'int'),
        'maximum' => array(PHP_INT_MAX, 'File size max in bytes', 'int'),
        'table' => array('sys_file', 'Table name for sum', 'string'),
        'field' => array('size', 'Table field name for sum', 'string'),
    );

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param mixed $value
     * @throws InvalidValidationOptionsException
     */
    protected function isValid($value)
    {
        if ($this->options['maximum'] < $this->options['minimum']) {
            throw new InvalidValidationOptionsException('The \'maximum\' is shorter than the \'minimum\' in the FileSizeValidator.', 1517096983);
        }

        if (is_object($value)) {
            if (!method_exists($value, '__toArray')) {
                $this->addError('The given object could not be converted to a array.', 1517097018);
                return;
            }
        } elseif (!is_array($value)) {
            $this->addError('The given value was not a valid array.', 1517097054);
            return;
        }

        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(
            ConnectionPool::class
        )->getQueryBuilderForTable($this->options['table']);

        $result = (int)$queryBuilder
            ->addSelectLiteral(
                $queryBuilder
                    ->expr()
                    ->sum($this->options['field'])
            )
            ->where(
                $queryBuilder
                    ->expr()
                    ->in(
                        'uid',
                        $queryBuilder->createNamedParameter($value, Connection::PARAM_INT_ARRAY)
                    )
            )
            ->from($this->options['table'])
            ->execute()
            ->fetchColumn(0);

        $isValid = true;
        if ($result < $this->options['minimum']) {
            $isValid = false;
        }
        if ($result > $this->options['maximum']) {
            $isValid = false;
        }

        $minString = GeneralUtility::formatSize($this->options['minimum']);
        $maxString = GeneralUtility::formatSize($this->options['maximum']);

        if ($isValid === false) {
            if ($this->options['minimum'] > 0 && $this->options['maximum'] < PHP_INT_MAX) {
                $this->addError(
                    $this->translateErrorMessage(
                        'validator.filesize.between',
                        'nl_auth',
                        [
                            $minString,
                            $maxString
                        ]
                    ),
                    1517097158,
                    [$this->options['minimum'], $this->options['maximum']]
                );
            } elseif ($this->options['minimum'] > 0) {
                $this->addError(
                    $this->translateErrorMessage(
                        'validator.filesize.less',
                        'nl_auth',
                        [
                            $minString
                        ]
                    ),
                    1517097171,
                    [$this->options['minimum']]
                );
            } else {
                $this->addError(
                    $this->translateErrorMessage(
                        'validator.filesize.exceed',
                        'nl_auth',
                        [
                            $maxString
                        ]
                    ),
                    1517097181,
                    [$this->options['maximum']]
                );
            }
        }
    }
}