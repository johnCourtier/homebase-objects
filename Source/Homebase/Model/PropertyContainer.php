<?php

namespace Homebase\Model;

use Homebase\Model\Property\InvalidValueException;
use ReflectionClass;

/**
 * This object has magic property annotation
 * - property has magic setter and getter, setter and getter method is prefered though
 * - property value type is checked when set
 * - property access is checked by magic setter and getter
 */
abstract class PropertyContainer
{
	/** @var Property[]|null */
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

			if (!isset($propertyQualities['name'])) {
				trigger_error('Unable to create Property. Property name was not provided. Parsed line was \''.$classPropertyQualities[0][$index].'\'', E_USER_ERROR);
			} else {
				$property = $this->createProperty($propertyQualities);
				$this->addProperty($property);
			}

			$index++;
		}

		// Error is generated for last child setup only
		if (empty($this->properties) && $className == get_class($this)) {
			trigger_error('No class properties were found in annotation. Without any property annotation, there is no reason why \''.get_class($this).'\' needs to extend \'AbstractObject\'.', E_USER_NOTICE);
		}
	}

	/**
	 * @param Property $property
	 */
	private function addProperty(Property $property)
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
	 * @return Property
	 */
	protected function createProperty(array $propertyQualities)
	{
		return StronglyTypedProperty::createProperty(
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
	 * @throws Homebase\Model\Property\InvalidValueException if value can not be set
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

	/** @var Property[] */
	protected function getProperties()
	{
		if ($this->properties === null) {
			$this->setupProperties();
		}

		return $this->properties;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function isPropertyReadable($name)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Unable to get property access of \''.$name.'\'. No such property is defined in \''.get_class($this).'\'. Use \''.get_class($this).'::propertyExists\' method to avoid this error.', E_USER_ERROR);
			return;
		}

		$property = $this->properties[$name];
		return $property->isReadable();
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function isPropertyWriteable($name)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Unable to get property access of \''.$name.'\'. No such property is defined in \''.get_class($this).'\'. Use \''.get_class($this).'::propertyExists\' method to avoid this error.', E_USER_ERROR);
			return;
		}

		$property = $this->properties[$name];
		return $property->isWriteable();
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function propertyExists($name)
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
			trigger_error('Unable to set property \''.$name.'\'. No such property is defined in \''.get_class($this).'\'. Use \''.get_class($this).'::propertyExists\' method to avoid this error.', E_USER_ERROR);
			return;
		}

		$property = $this->properties[$name];
		if (!$property->isWriteable()) {
			trigger_error('Unable to set property \''.$name.'\'. No such property is writeable in \''.get_class($this).'\' class. Use \''.get_class($this).'::isPropertyWriteable\' method to avoid this error.', E_USER_ERROR);
			return;
		}

		$methodName = 'set'.ucfirst($name);
		if (is_callable(array($this, $methodName))) {
			return call_user_func_array(array($this, $methodName), array($value));
		}

		try {
			$this->setPropertyValue($name, $value);
		} catch (InvalidValueException $exception) {
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
			trigger_error('Unable to get property \''.$name.'\'. No such property is defined in \''.get_class($this).'\'. Use \''.get_class($this).'::propertyExists\' method to avoid this error.', E_USER_ERROR);
			return NULL;
		}

		$property = $this->properties[$name];
		if (!$property->isReadable()) {
			trigger_error('Unable to get property \''.$name.'\'. No such property is readable in \''.get_class($this).'\' class. Use \''.get_class($this).'::isPropertyReadable\' method to avoid this error.', E_USER_ERROR);
			return NULL;
		}

		$methodName = 'get'.ucfirst($name);
		if (is_callable(array($this, $methodName))) {
			return call_user_func_array(array($this, $methodName));
		}

		if ($property->isValueSet()) {
			return $property->getValue();
		}

		trigger_error('Unable to get property \''.$name.'\'. No such property was yet set in \''.get_class($this).'\' class.', E_USER_ERROR);
		return NULL;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		if (!$this->propertyExists($name)) {
			trigger_error('Class \''.get_class($this).'\' has no \''.$name.'\' property defined. Use \''.get_class($this).'::propertyExists\' method to avoid this warning.', E_USER_WARNING);
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
			trigger_error('Class \''.get_class($this).'\' has no \''.$name.'\' property defined. Use \''.get_class($this).'::propertyExists\' method to avoid this error.', E_USER_WARNING);
			return;
		}

		$property = $this->properties[$name];
		if (!$property->isWriteable()) {
			trigger_error('Unable to unset property \''.$name.'\'. No such property is writeable in \''.get_class($this).'\' class. Use \''.get_class($this).'::isPropertyWriteable\' method to avoid this error.', E_USER_ERROR);
			return;
		}

		$property->unsetValue();
	}
}
