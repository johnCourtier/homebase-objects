<?php

namespace Homebase\Model;

interface IProperty
{
	/**
	 * @return string[]|null valid types for value
	 */
	public function getTypes();

	/**
	 * @return string|null
	 */
	public function getName();

	/**
	 * @return string|null
	 */
	public function getDescription();

	/**
	 * @return mixed|null
	 */
	public function getValue();

	/**
	 * @param string[]|null $types valid types for value
	 */
	public function setTypes($types);

	/**
	 * @param string $name
	 * @throws InvalidArgumentException if $name is empty
	 */
	public function setName($name);

	/**
	 * @param string $description
	 */
	public function setDescription($description);

	/**
	 * @param mixed $value
	 * @return string actual type of $value
	 */
	public function getValueType($value);

	/**
	 * @param mixed|null $value
	 * @throws InvalidArgumentException if value can not be set
	 */
	public function setValue($value);

	public function unsetValue();

	/**
	 * @return bool
	 */
	public function isWriteable();

	/**
	 * @return bool
	 */
	public function isReadable();

	/**
	 * @return bool
	 */
	public function isValueSet();
}
