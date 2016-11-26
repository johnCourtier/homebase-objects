<?php

namespace Homebase\Model;

interface Mutable
{
	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function isPropertyChanged($propertyName);
}
