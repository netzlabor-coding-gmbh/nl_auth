<?php

namespace NL\NlAuth\Domain\Validator;


use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use NL\NlAuth\Validation\ValidatorResolver;

class AbstractEntityValidator extends GenericObjectValidator implements ValidatorInterface
{
    const SELF_VALIDATION = '_self';

    /**
     * @var ObjectStorage
     */
    static protected $instancesCurrentlyUnderValidation;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = null;

    /**
     * @var array
     */
    protected $frameworkConfiguration = [];

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * Name of the current field to validate
     *
     * @var string
     */
    protected $currentPropertyName = '';

    /**
     * @var array
     */
    protected $currentValidatorOptions = [];

    /**
     * Model that gets validated currently
     *
     * @var AbstractEntity
     */
    protected $model;

    /**
     * @var array
     */

    protected $supportedOptions = array(
        'settingsPath' => array('', 'Validation rules settings path', 'string', true),
    );

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ConfigurationManager $configurationManager
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function injectConfigurationManager(ConfigurationManager $configurationManager) {
        /** @var ConfigurationManager configurationManager */
        $this->configurationManager = $configurationManager;

        $this->frameworkConfiguration = $this->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $this->settings = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                $this->frameworkConfiguration['extensionName'],
                $this->frameworkConfiguration['pluginName']
        );
    }

    /**
     * @param Result $result
     */
    public function injectResult(Result $result)
    {
        $this->result = $result;
    }

    /**
     * @param ValidatorResolver $validatorResolver
     */
    public function injectValidatorResolver(ValidatorResolver $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }

    /**
     * @param mixed $object
     * @return Result
     */
    public function validate($object)
    {
        /** @var Result $messages */
        $messages = $this->objectManager->get(Result::class);

        if (self::$instancesCurrentlyUnderValidation === null) {
            self::$instancesCurrentlyUnderValidation = new ObjectStorage();
        }

        if ($object === null) {
            return $messages;
        }

        if (!$this->canValidate($object)) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            /** @var \TYPO3\CMS\Extbase\Error\Error $error */
            $error = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Error\Error::class,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'validator.abstractEntity.notValidatable',
                    'nl_auth'
                ),
                1530096635
            );

            $messages->addError($error);

            return $messages;
        }

        if (self::$instancesCurrentlyUnderValidation->contains($object)) {
            return $messages;
        } else {
            self::$instancesCurrentlyUnderValidation->attach($object);
        }

        $this->model = $object;
        $propertyValidators = $this->getValidationRulesFromSettings();

        foreach ($propertyValidators as $propertyName => $validatorsNames) {
            if ($propertyName !== static::SELF_VALIDATION && !property_exists($object, $propertyName)) {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                /** @var \TYPO3\CMS\Extbase\Error\Error $error */
                $error = $this->objectManager->get(
                    \TYPO3\CMS\Extbase\Error\Error::class,
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'validator.abstractEntity.propertyDoesNotExist',
                        'nl_auth'
                    ),
                    1530108947
                );

                $messages->addError($error);
            } else {
                $this->currentPropertyName = $propertyName;

                if ($propertyName === static::SELF_VALIDATION) {
                    $this->applyValidation(
                        $object,
                        (array) $validatorsNames,
                        $messages
                    );
                } else {
                    $this->applyValidation(
                        $this->getPropertyValue($object, $propertyName),
                        (array) $validatorsNames,
                        $messages->forProperty($propertyName)
                    );
                }
            }
        }

        self::$instancesCurrentlyUnderValidation->detach($object);

        return $messages;
    }
    /**
     * Checks if the specified property of the given object is valid, and adds
     * found errors to the $messages object.
     *
     * @param mixed $value The value to be validated
     * @param array $validatorNames Contains an array with validator names
     * @param Result $messages the result object
     */
    protected function applyValidation($value, array $validatorNames, Result $messages)
    {
        foreach ($validatorNames as $validatorName) {
            $messages->merge(
                $this->getValidator($validatorName)->validate($value)
            );
        }
    }
    /**
     * Checks if validator can validate the object
     *
     * @param AbstractEntity
     *
     * @return bool
     */
    public function canValidate($object)
    {
        return $object instanceof AbstractEntity;
    }

    /**
     * Get validation rules from settings
     * Warning: Don't remove the validators added in this method
     *          These prevent that editing others data is possible
     */
    protected function getValidationRulesFromSettings()
    {
        $rules = ObjectAccess::getPropertyPath($this->settings, $this->options['settingsPath']) ?: [];

        return $rules;
    }

    /**
     * Parse the rule and instantiate an validator with the name and the options
     *
     * @param string $rule
     *
     * @return AbstractValidator
     */
    protected function getValidator($rule)
    {
        $currentValidator = $this->parseRule($rule);

        $this->currentValidatorOptions = (array) $currentValidator['validatorOptions'];

        /** @var AbstractValidator $validator */
        $validator = $this->validatorResolver->createValidator(
            $currentValidator['validatorName'],
            $this->currentValidatorOptions
        );

        if (method_exists($validator, 'setModel')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $validator->setModel($this->model);
        }

        if (method_exists($validator, 'setPropertyName')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $validator->setPropertyName($this->currentPropertyName);
        }

        return $validator;
    }

    /**
     * @param string $rule
     * @return array
     */
    protected function parseRule($rule)
    {
        $parsedRules = $this->validatorResolver->getParsedValidatorAnnotation($rule);

        return current($parsedRules['validators']);
    }
}