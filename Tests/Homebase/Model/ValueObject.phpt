<?php

use Homebase\Model\ValueObject;
use Tester\Assert;
use Tester\TestCase;

require substr(__DIR__, 0, strpos(__DIR__, 'Tests')+5) . '/../vendor/autoload.php';

/**
 * @property $immutableProperty
 */
class NewValueObject extends ValueObject
{
}

class ValueObjectTest extends TestCase
{
	/** @var ValueObject */
	protected $valueObject;

	public function setUp()
	{
		Assert::noError(function() {
			$this->valueObject = new NewValueObject();
		});
	}

	/**
	 * @testCase
	 */
	public function testPropertyImmutability()
	{
		Assert::same(FALSE, isset($this->valueObject->immutableProperty));
		Assert::same(TRUE, $this->valueObject->isPropertyWriteable('immutableProperty'));
		$this->valueObject->immutableProperty = 'voodoo';
		Assert::same(FALSE, $this->valueObject->isPropertyWriteable('immutableProperty'));
		Assert::same(TRUE, isset($this->valueObject->immutableProperty));
		Assert::error(function() {
			$this->valueObject->immutableProperty = 'boo';
		}, 'E_USER_ERROR', 'Unable to set property \'immutableProperty\' again. Property of \'NewValueObject\' can be set just once. Use \''.get_class($this->valueObject).'::isPropertyWriteable\' method to avoid this error.');
		Assert::error(function() {
			unset($this->valueObject->immutableProperty);
		}, 'E_USER_ERROR', 'Unable to unset property \'immutableProperty\'. Property of \'NewValueObject\' can not be unset.');
	}
}

$valueObjectTest = new ValueObjectTest();
$valueObjectTest->run();