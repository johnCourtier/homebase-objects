<?php

namespace Homebase\Model;

abstract class Entity extends PropertyContainer implements Mutable
{
	/** @var array array(string<name> => mixed<value>) */
	protected $originalValues;

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$isOriginalValue = !$this->isOriginalValueSet($name);

		parent::__set($name, $value);

		if ($isOriginalValue) {
			$this->setOriginalValue($name, $this->getPropertyValue($name));
		}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	protected function isOriginalValueSet($name)
	{
		return isset($this->originalValues[$name]);
	}

	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function isPropertyChanged($propertyName)
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
	protected function setOriginalValue($name, $value)
	{
		$this->originalValues[$name] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setPropertyValue($name, $value)
	{
		$isOriginalValue = !$this->isOriginalValueSet($name);

		parent::setPropertyValue($name, $value);

		if ($isOriginalValue) {
			$this->setOriginalValue($name, $value);
		}
	}
}
