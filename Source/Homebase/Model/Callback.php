<?php

namespace Homebase\Model;

class Callback
{
	/** @var callback */
	private $callback;

	/** @var array */
	private $arguments;

	public function __construct(callable $callback, array $arguments = array())
	{
		$this->callback = $callback;
		$this->arguments = $arguments;
	}

	/**
	 * @return mixed
	 */
	public function getResult()
	{
		return call_user_func_array($this->callback, $this->arguments);
	}
}
