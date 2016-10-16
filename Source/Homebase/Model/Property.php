<?php

namespace Homebase\Model;

use InvalidArgumentException;
use Traversable;

class Property implements IProperty
{
	const ACCESS_READ = 0b01;
	const ACCESS_WRITE = 0b10;

	/** @var string|null */
	protected $type;

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
	 * @param string $type
	 * @param string $description
	 */
	protected function __construct(
		$name,
		$access = null,
		$type = null,
		$description = null
	) {
		$this->setAccess($access);
		if ($type !== null) {
			$this->setTypes(explode('|', $type));
		} else {
			$this->setTypes($type);
		}
		$this->setName($name);
		$this->setDescription($description);
	}

	/**
	 * @param string $name
	 * @param string|null $access
	 * @param string|null $type
	 * @param string|null $description
	 * @return Property
	 */
	public static function createProperty(
		$name,
		$access = null,
		$type = null,
		$description = null
	) {
		return new Property($name, $access, $type, $description);
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
	 * @param string[]|null $types
	 */
	public function setTypes($types)
	{
		$this->types = $types;
	}

	/**
	 * @param string $name
	 * @throws InvalidArgumentException if $name is empty
	 */
	public function setName($name)
	{
		if (empty($name)) {
			throw new InvalidArgumentException('Unable to set empty name.');
		}
		$this->name = $name;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
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
		} else {
			return gettype($value);
		}
	}

	/**
	 * @param mixed|null $value
	 * @throws InvalidArgumentException if value can not be set
	 */
	public function setValue($value)
	{
		$types = $this->getTypes();

		if ($types === null) {
			$this->value = $value;
			$this->isValueSet = true;
			return;
		}

		foreach ($types as $type) {
			if ($this->isTypeAndValueScalar($type, $value)
				|| $this->isTypeAndValueScalarArray($type, $value)
				|| $this->isTypeAndValueObject($type, $value)
				|| $this->isTypeAndValueObjectArray($type, $value)
			) {
				$this->value = $value;
				$this->isValueSet = true;
				return;
			}
		}

		throw new InvalidArgumentException('Unable to set value for \''.$this->getName().'\' property. Value is supposed to be \''.implode('\' or \'', $types).'\', but actually is \''.$this->getValueType($value).'\'.');
	}

	public function unsetValue()
	{
		$this->value = null;
		$this->isValueSet = false;
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
		$objectType = $this->isTypeObjectArray($type);
		if ($objectType) {
			if (!is_array($value) && !($value instanceof Traversable)) {
				return FALSE;
			}

			foreach ($value as $item) {
				if (!$this->isTypeAndValueObject($objectType, $item)) {
					return FALSE;
				}
			}

			return TRUE;
		}
	}

	/**
	 * @param type $type
	 * @param Traversable $value
	 * @return bool
	 */
	protected function isTypeAndValueScalarArray($type, $value)
	{
		$scalarType = $this->isTypeScalarArray($type);
		if ($scalarType) {
			if (!is_array($value) && !($value instanceof Traversable)) {
				return FALSE;
			}

			foreach ($value as $item) {
				if (!$this->isTypeAndValueScalar($scalarType, $item)) {
					return FALSE;
				}
			}

			return TRUE;
		}

		return FALSE;
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

		return false;
	}

	/**
	 * @param string $type
	 * @return string|false fullClassName
	 */
	protected function isTypeObjectArray($type) {
		if (strpos($type, '[]') === strlen($type)-2) {
			return substr($type, 0, -2);
		}

		return false;
	}

	/**
	 * @param string $type
	 * @return string|boolean
	 */
	protected function isTypeScalarArray($type) {
		if (strpos($type, '[]') === strlen($type)-2) {
			return $this->isTypeScalar(substr($type, 0, -2));
		}

		return false;
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return bool
	 * @throws InvalidArgumentException if no function exists for checking
	 */
	protected function isValueScalarType($type, $value)
	{
		$funtionName = 'is_'.$type;
		if (!function_exists($funtionName)) {
			throw new InvalidArgumentException('Unable to check scalar type of value for \''.$this->getName().'\' property. No function \''.$funtionName.'\' exist for that purpose.');
		}

		return call_user_func_array($funtionName, array($value));
	}

	/**
	 * @param string|null $access
	 */
	protected function setAccess($access = null)
	{
		if ($access === 'read') {
			$this->access = self::ACCESS_READ;
		} elseif ($access === 'write') {
			$this->access = self::ACCESS_WRITE;
		} else {
			$this->access = self::ACCESS_READ | self::ACCESS_WRITE;
		}
	}

	/**
	 * @return bool
	 */
	public function isWriteable()
	{
		return $this->access & self::ACCESS_WRITE;
	}

	/**
	 * @return bool
	 */
	public function isReadable()
	{
		return $this->access & self::ACCESS_READ;
	}

	/**
	 * @return bool
	 */
	public function isValueSet()
	{
		return $this->isValueSet;
	}
}
