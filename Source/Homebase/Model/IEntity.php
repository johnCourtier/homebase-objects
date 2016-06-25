<?php

namespace Homebase\Model;

interface IEntity
{
	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function isChanged($propertyName);
}
