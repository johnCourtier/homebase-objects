<?php

namespace Homebase\Model;

abstract class ValueObject extends PropertyContainer
{
	/**
	 * @param string $name
	 * @return bool
	 */
	public function isPropertyWriteable($name)
	{
		$isPropertyWriteable = parent::isPropertyWriteable($name);

		$properties = $this->getProperties();
		$property = $properties[$name];
		return $isPropertyWriteable && !$property->isValueSet();
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setPropertyValue($name, $value)
	{
		if (isset($this->$name)) {
			trigger_error('Unable to set property \''.$name.'\' again. Property of \''.get_class($this).'\' can be set just once. Use \''.get_class($this).'::isPropertyWriteable\' method to avoid this error.', E_USER_ERROR);
			return;
		}

		parent::setPropertyValue($name, $value);
	}

	/**
	 * @param string $name
	 */
	public final function __unset($name)
	{
		trigger_error('Unable to unset property \''.$name.'\'. Property of \''.get_class($this).'\' can not be unset.', E_USER_ERROR);
	}
}
