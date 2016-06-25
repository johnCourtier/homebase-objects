<?php

use Homebase\Model\AbstractValueObject;
use Tester\Assert;
use Tester\TestCase;

require substr(__DIR__, 0, strpos(__DIR__, 'Tests')+5) . '/../vendor/autoload.php';

/**
 * @property $immutableProperty
 */
class ValueObject extends AbstractValueObject
{
}

class AbstractValueObjectTest extends TestCase
{
	/** @var ValueObject */
	protected $valueObject;

	public function setUp()
	{
		Assert::noError(function() {
			$this->valueObject = new ValueObject();
		});
	}

	/**
	 * @testCase
	 */
	public function testPropertyImmutability()
	{
		Assert::same(FALSE, isset($this->valueObject->immutableProperty));
		$this->valueObject->immutableProperty = 'voodoo';
		Assert::same(TRUE, isset($this->valueObject->immutableProperty));
		Assert::error(function() {
			$this->valueObject->immutableProperty = 'boo';
		}, 'E_USER_ERROR', 'Unable to set property \'immutableProperty\' again. Property of \'ValueObject\' can be set just once. Use isset to avoid this error.');
		Assert::error(function() {
			unset($this->valueObject->immutableProperty);
		}, 'E_USER_ERROR', 'Unable to unset property \'immutableProperty\'. Property of \'ValueObject\' can not be unset.');
	}
}

$abstractValueObjectTest = new AbstractValueObjectTest();
$abstractValueObjectTest->run();