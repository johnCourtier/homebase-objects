<?php

namespace Homebase\Model;

use Homebase\Model\Property\InvalidValueException;
use Traversable;

class StronglyTypedProperty implements Property
{
	const ACCESS_READ = 0b01;
	const ACCESS_WRITE = 0b10;

	/** @var string[]|null */
	protected $types;

	/** @var string */
	protected $name;

	/** @var string|null */
	protected $description;

	/** @var mixed|null */
	protected $value;

	/** @var int */
	protected $access;

	/** @var bool */
	protected $isValueSet = FALSE;

	/**
	 * @param string $name
	 * @param string $access
	 * @param string|string[] $type
	 * @param string $description
	 */
	protected function __construct(
		$name,
		$access = null,
		$type = null,
		$description = null
	) {
		$this->setAccess($access);
		$this->setTypes($type);
		$this->setName($name);
		$this->setDescription($description);
	}

	/**
	 * @param string $name
	 * @param string|null $access
	 * @param string|null $type
	 * @param string|null $description
	 * @return static
	 */
	public static function createProperty(
		$name,
		$access = null,
		$type = null,
		$description = null
	) {
		return new static($name, $access, $type, $description);
	}

	/**
	 * @return string[]|null
	 */
	public function getTypes()
	{
		return $this->types;
	}

	/**
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return mixed|null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string|string[]|null $types
	 */
	protected function setTypes($types)
	{
		if (is_string($types)) {
			$this->types = explode('|', $types);
		} else {
			$this->types = $types;
		}
	}

	/**
	 * @param string $name
	 */
	protected function setName($name)
	{
		if (empty($name)) {
			trigger_error('Unable to set empty property name.', E_USER_ERROR);
		}
		$this->name = $name;
	}

	/**
	 * @param string|null $description
	 */
	protected function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getValueType($value)
	{
		if (is_object($value)) {
			return get_class($value);
		}

		return gettype($value);
	}

	/**
	 * @param mixed|null $value
	 * @throws Homebase\Model\Property\InvalidValueException if value can not be set
	 */
	public function setValue($value)
	{
		$types = $this->getTypes();

		if ($types === NULL) {
			$this->value = $value;
			$this->isValueSet = TRUE;
			return;
		}

		foreach ($types as $type) {
			if ($this->isTypeAndValueScalar($type, $value)
				|| $this->isTypeAndValueScalarArray($type, $value)
				|| $this->isTypeAndValueObject($type, $value)
				|| $this->isTypeAndValueObjectArray($type, $value)
			) {
				$this->value = $value;
				$this->isValueSet = TRUE;
				return;
			}
		}

		throw new InvalidValueException('Unable to set value of \''.$this->getName().'\' property. Value is supposed to be \''.implode('\' or \'', $types).'\', but actually is \''.$this->getValueType($value).'\'.');
	}

	public function unsetValue()
	{
		$this->value = NULL;
		$this->isValueSet = FALSE;
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return bool
	 */
	protected function isTypeAndValueScalar($type, $value)
	{
		return ($this->isTypeScalar($type) && $this->isValueScalarType($type, $value));
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return bool
	 */
	protected function isTypeAndValueObject($type, $value)
	{
		return ($value instanceOf $type);
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return bool
	 */
	protected function isTypeAndValueObjectArray($type, $value)
	{
		if (!is_array($value) && !($value instanceof Traversable)) {
			return FALSE;
		}

		$objectType = $this->isTypeObjectArray($type);
		if ($objectType === FALSE) {
			return FALSE;
		}

		foreach ($value as $item) {
			if (!$this->isTypeAndValueObject($objectType, $item)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * @param type $type
	 * @param Traversable $value
	 * @return bool
	 */
	protected function isTypeAndValueScalarArray($type, $value)
	{
		if (!is_array($value) && !($value instanceof Traversable)) {
			return FALSE;
		}

		$scalarType = $this->isTypeScalarArray($type);
		if ($scalarType === FALSE) {
			return FALSE;
		}

		foreach ($value as $item) {
			if (!$this->isTypeAndValueScalar($scalarType, $item)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * @param string $type
	 * @return string|false scalarType
	 */
	protected function isTypeScalar($type)
	{
		$funtionName = 'is_'.$type;
		if (function_exists($funtionName)) {
			return $type;
		}

		return FALSE;
	}

	/**
	 * @param string $type
	 * @return string|false fullClassName
	 */
	protected function isTypeObjectArray($type) {
		if (strpos($type, '[]') === strlen($type)-2) {
			return substr($type, 0, -2);
		}

		return FALSE;
	}

	/**
	 * @param string $type
	 * @return string|false
	 */
	protected function isTypeScalarArray($type) {
		if (strpos($type, '[]') === strlen($type)-2) {
			return $this->isTypeScalar(substr($type, 0, -2));
		}

		return FALSE;
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return bool
	 */
	protected function isValueScalarType($type, $value)
	{
		$funtionName = 'is_'.$type;
		if (!function_exists($funtionName)) {
			trigger_error('Unable to check scalar type of value for \''.$this->getName().'\' property. No function \''.$funtionName.'\' exist for that purpose.', E_USER_ERROR);
			return FALSE;
		}

		return call_user_func_array($funtionName, array($value));
	}

	/**
	 * @param string|null $access
	 */
	protected function setAccess($access = null)
	{
		if ($access === 'read') {
			$this->access = static::ACCESS_READ;
		} elseif ($access === 'write') {
			$this->access = static::ACCESS_WRITE;
		} else {
			$this->access = static::ACCESS_READ | static::ACCESS_WRITE;
		}
	}

	/**
	 * @return bool
	 */
	public function isWriteable()
	{
		return (bool) $this->access & static::ACCESS_WRITE;
	}

	/**
	 * @return bool
	 */
	public function isReadable()
	{
		return (bool) $this->access & static::ACCESS_READ;
	}

	/**
	 * @return bool
	 */
	public function isValueSet()
	{
		return $this->isValueSet;
	}
}
