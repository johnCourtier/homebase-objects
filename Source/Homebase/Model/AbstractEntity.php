<?php

namespace Homebase\Model;

abstract class AbstractEntity extends AbstractObject implements IEntity
{
	/** @var array array(string<name> => mixed<value>) */
	protected $originalValues;

	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function isChanged($propertyName)
	{
		return (isset($this->$propertyName)
			&& isset($this->originalValues[$propertyName])
			&& $this->originalValues[$propertyName] !== $this->$propertyName
		);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$isOriginalValue = !isset($this->originalValues[$name]);

		parent::__set($name, $value);

		if ($isOriginalValue) {
			$this->originalValues[$name] = $this->getPropertyValue($name);
		}
	}
}
