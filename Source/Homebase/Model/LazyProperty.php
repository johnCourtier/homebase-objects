<?php

namespace Homebase\Model;

use Homebase\Model\Property\InvalidValueException;

class LazyProperty extends StronglyTypedProperty
{
	/** @var Callback|null */
	protected $callback;

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		if ($this->isCallbackSet()) {
			$callbackResult = $this->callback->getResult();
			try {
				$this->setValue($callbackResult);
			} catch (InvalidValueException $exception) {
				throw new InvalidValueException('Unable to get value of \''.$this->getName().'\' property. Lazy property callback result was evaluated, but can not be set.', 500, $exception);
			}
			$this->callback = NULL;
		}
		return parent::getValue();
	}

	/**
	 * @return bool
	 */
	public function isCallbackSet()
	{
		return $this->callback !== NULL;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value)
	{
		if (!$this->isValueSet() && $value instanceof Callback) {
			if ($this->isCallbackSet()) {
				throw new InvalidValueException('Unable to set callback for \''.$this->getName().'\' property. Callback is already set. Use \''.get_class($this).'::isCallbackSet\' to avoid this error.');
			}
			$this->callback = $value;
			$this->isValueSet = TRUE;
		} else {
			parent::setValue($value);
		}
	}
}
