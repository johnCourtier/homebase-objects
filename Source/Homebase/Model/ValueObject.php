<?php

namespace Homebase\Model;

abstract class ValueObject extends PropertyContainer
{
	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setPropertyValue($name, $value)
	{
		if (isset($this->$name)) {
			trigger_error('Unable to set property \''.$name.'\' again. Property of \''.get_class($this).'\' can be set just once. Use isset to avoid this error.', E_USER_ERROR);
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
