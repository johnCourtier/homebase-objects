<?php

namespace Homebase\Model;

use InvalidArgumentException;
use LogicException;
use ReflectionClass;

/**
 * This object has magic property annotation
 * - property has magic setter and getter, setter and getter method is prefered though
 * - property value type is checked when set
 * - property access is checked by magic setter and getter
 */
abstract class AbstractObject
{
	/** @var IProperty[]|null */
	private $properties = null;

	/**
	 * Provides matches in class annotation
	 * @param string $className
	 * @return array
	 */
	private function getClassProperties($className)
	{
		$reflection = new ReflectionClass($className);
		$docComment = $reflection->getDocComment();

		$classPropertyQualities = array();
		preg_match_all($this->getParsingPattern(), $docComment, $classPropertyQualities);

		return $classPropertyQualities;
	}

	/**
	 * Provides pattern for class annotation parsing
	 * @return string
	 */
	protected function getParsingPattern()
	{
		return '/@property-?(?P<access>\\S+)?\\s*(?P<type>\\S+)?\\s+\\$(?P<name>\\S+)\\s*(?P<description>\\S+)?$/m';
	}

	/**
	 * Calls annotation parsing and processes matches
	 * @param string $className
	 */
	private function setupProperties($className = null)
	{
		if ($className === null) {
			$className = get_class($this);
		}

		$parent = get_parent_class($className);
		if ($parent === FALSE) {
			$this->properties = array();
		} else {
			$this->setupProperties($parent);
		}

		$classPropertyQualities = $this->getClassProperties($className);

		$propertyCount = count($classPropertyQualities[0]);
		$index = 0;

		while ($index < $propertyCount) {
			$propertyQualities = array();

			foreach ($classPropertyQualities as $propertyQualityName => $classPropertyQualitiesMatch) {
				if (!is_string($propertyQualityName)) {
					continue;
				}

				if (!empty($classPropertyQualitiesMatch[$index])) {
					$propertyQualities[$propertyQualityName] = $classPropertyQualitiesMatch[$index];
				}
			}

			try {
				$property = $this->createProperty($propertyQualities);
				$this->addProperty($property);
			} catch (LogicException $exception) {
				trigger_error($exception->getMessage().' Parsed line was \''.$classPropertyQualities[0][$index].'\'', E_USER_ERROR);
			}

			$index++;
		}

		// Error is generated for last child setup only
		if (empty($this->properties) && $className == get_class($this)) {
			trigger_error('No class properties were found in annotation. Without any property annotation, there is no reason why \''.get_class($this).'\' needs to extend \'AbstractObject\'.', E_USER_NOTICE);
		}
	}

	/**
	 * @param IProperty $property
	 */
	private function addProperty(IProperty $property)
	{
		if ($this->propertyExists($property->getName())) {
			$parentClassName = get_parent_class(get_class($this));
			trigger_error('Property \''.$property->getName().'\' was already defined in \''.$parentClassName.'\'. Definition in \''.get_class($this).'\' has no effect.', E_USER_WARNING);
			return;
		}

		$this->properties[$property->getName()] = $property;
	}

	/**
	 * @param array $propertyQualities array(string<qualityName> => string<qualityValue>) matches of annotation parsing
	 * @return IProperty
	 * @throws LogicException if name key is missing
	 */
	protected function createProperty(array $propertyQualities)
	{
		if (!isset($propertyQualities['name'])) {
			throw new LogicException('Unable to create Property. Property name was not parsed.');
		}

		return Property::createProperty(
			$propertyQualities['name'],
			isset($propertyQualities['access']) ? $propertyQualities['access'] :null,
			isset($propertyQualities['type']) ? $propertyQualities['type'] :null,
			isset($propertyQualities['description']) ? $propertyQualities['description'] :null
		);
	}

	/**
	 * Property access is ignored
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setPropertyValue($name, $value)
	{
		if ($this->properties === null) {
			$this->setupProperties();
		}

		if (!isset($this->properties[$name])) {
			trigger_error('Unable to set property \''.$name.'\'. No such property was mentioned in class annotation of \''.get_class($this).'\'. To avoid this error, please call \'propertyExists\' method first.', E_USER_ERROR);
			return;
		}

		$this->properties[$name]->setValue($value);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	protected function getPropertyValue($name)
	{
		if ($this->properties === null) {
			$this->setupProperties();
		}

		if (!isset($this->properties[$name])) {
			trigger_error('Unable to get property \''.$name.'\'. No such property was mentioned in class annotation of \''.get_class($this).'\'. To avoid this error, please call \'propertyExists\' method first.', E_USER_ERROR);
			return NULL;
		}

		return $this->properties[$name]->getValue();
	}

	/** @var IProperty[]|null */
	protected function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	protected function propertyExists($name)
	{
		if ($this->properties === null) {
			$this->setupProperties();
		}

		return isset($this->properties[$name]);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Unable to set property \''.$name.'\'. No such property is defined in \''.get_class($this).'\'.', E_USER_ERROR);
			return;
		}

		$property = $this->properties[$name];
		if (!$property->isWriteable()) {
			trigger_error('Unable to set property \''.$name.'\'. Property of \''.get_class($this).'\' is not writeable.', E_USER_ERROR);
			return;
		}

		$methodName = 'set'.ucfirst($name);
		if (is_callable(array($this, $methodName))) {
			return call_user_func_array(array($this, $methodName), array($value));
		}

		try {
			$this->setPropertyValue($name, $value);
		} catch (InvalidArgumentException $exception) {
			trigger_error($exception->getMessage(), E_USER_ERROR);
		}
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Unable to get property \''.$name.'\'. No such property is defined in \''.get_class($this).'\'.', E_USER_ERROR);
			return NULL;
		}

		$property = $this->properties[$name];
		if (!$property->isReadable()) {
			trigger_error('Unable to get property \''.$name.'\'. Property of \''.get_class($this).'\' is not readable.', E_USER_ERROR);
			return NULL;
		}

		$methodName = 'get'.ucfirst($name);
		if (is_callable(array($this, $methodName))) {
			return call_user_func_array(array($this, $methodName));
		}

		if ($property->isValueSet()) {
			return $property->getValue();
		}

		trigger_error('Unable to get property \''.$name.'\'. Property of \''.get_class($this).'\' was not yet set.', E_USER_ERROR);
		return NULL;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Class \''.get_class($this).'\' has no \''.$name.'\' property defined.', E_USER_WARNING);
			return FALSE;
		}

		$property = $this->properties[$name];
		return $property->isValueSet();
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Class \''.get_class($this).'\' has no \''.$name.'\' property defined.', E_USER_WARNING);
			return;
		}

		$property = $this->properties[$name];
		if (!$property->isWriteable()) {
			trigger_error('Unable to unset property \''.$name.'\'. Property of \''.get_class($this).'\' is not writeable.', E_USER_ERROR);
			return;
		}

		$property->unsetValue();
	}
}
