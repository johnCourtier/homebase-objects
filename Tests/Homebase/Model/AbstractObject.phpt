<?php

use Homebase\Model\AbstractObject;
use Tester\Assert;
use Tester\TestCase;

require substr(__DIR__, 0, strpos(__DIR__, 'Tests')+5) . '/../vendor/autoload.php';

/**
 * @property-read string $stringPropertyRead
 * @property-write string $stringPropertyWrite
 * @property string $stringProperty
 * @property int $intProperty
 * @property numeric $numericProperty
 * @property null $null
 * @property string|null $stringNullProperty
 * @property DateTime $dateTime
 * @property $mixed
 * @property string[] $stringsProperty
 * @property DateTime[] $dateTimes
 */
class Object extends AbstractObject
{
}

/**
 * @property bool $isExtended
 */
class ObjectExtended extends Object
{

}

class AbstractObjectTest extends TestCase
{
	/** @var Object */
	protected $object;

	public function setUp()
	{
		Assert::noError(function() {
			$this->object = new Object();
		});
	}

	/**
	 * @testCase
	 */
	public function testPropertyExistence()
	{
		$this->object->stringProperty = 'voodoo';
		Assert::error(function() {
			return $this->object->nonProperty;
		}, 'E_USER_ERROR', 'Unable to get property \'nonProperty\'. No such property is defined in \''.get_class($this->object).'\'.');
		Assert::error(function() {
			return isset($this->object->nonProperty);
		}, 'E_USER_WARNING', 'Class \''.get_class($this->object).'\' has no \'nonProperty\' property defined.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyAccessWriteOnly()
	{
		Assert::error(function() {
			return $this->object->stringPropertyWrite;
		}, 'E_USER_ERROR', 'Unable to get property \'stringPropertyWrite\'. Property of \''.get_class($this->object).'\' is not readable.');
		$this->object->stringPropertyWrite = 'voodoo';
	}

	/**
	 * @testCase
	 */
	public function testPropertyAccessReadOnly()
	{
		Assert::error(function() {
			return $this->object->stringPropertyRead;
		}, 'E_USER_ERROR', 'Unable to get property \'stringPropertyRead\'. Property of \''.get_class($this->object).'\' was not yet set.');
		Assert::error(function() {
			$this->object->stringPropertyRead = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set property \'stringPropertyRead\'. Property of \''.get_class($this->object).'\' is not writeable.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyCombinedAccess()
	{
		Assert::error(function() {
			return $this->object->stringProperty;
		}, 'E_USER_ERROR', 'Unable to get property \'stringProperty\'. Property of \''.get_class($this->object).'\' was not yet set.');
		$this->object->stringProperty = 'voodoo';
		Assert::same('voodoo', $this->object->stringProperty);
	}

	/**
	 * @testCase
	 */
	public function testPropertyScalarTypeSetter()
	{
		Assert::error(function() {
			$this->object->stringProperty = 666;
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'string\', but actually is \'integer\'.');
		Assert::error(function() {
			$this->object->stringProperty = null;
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'string\', but actually is \'NULL\'.');
		$this->object->stringProperty = 'voodoo';
		Assert::same('voodoo', $this->object->stringProperty);

		Assert::error(function() {
			$this->object->intProperty = '666';
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'int\', but actually is \'string\'.');
		Assert::error(function() {
			$this->object->intProperty = null;
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'int\', but actually is \'NULL\'.');
		$this->object->intProperty = 666;

		$this->object->numericProperty = 666;
		$this->object->numericProperty = '666';
		Assert::error(function() {
			$this->object->numericProperty = null;
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'numeric\', but actually is \'NULL\'.');

		$this->object->null = null;
		Assert::error(function() {
			$this->object->null = 'null';
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'null\', but actually is \'string\'.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyCombinedTypeSetter()
	{
		Assert::error(function() {
			$this->object->stringNullProperty = 666;
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'string\' or \'null\', but actually is \'integer\'.');
		$this->object->stringNullProperty = '666';
		$this->object->stringNullProperty = null;
	}

	/**
	 * @testCase
	 */
	public function testPropertyObjectTypeSetter()
	{
		Assert::error(function() {
			$this->object->dateTime = date('Y-m-d H:i:s');
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'DateTime\', but actually is \'string\'.');
		$this->object->dateTime = new DateTime();
	}

	/**
	 * @testCase
	 */
	public function testPropertyIssetUnset()
	{
		Assert::same(FALSE, isset($this->object->stringProperty));
		$this->object->stringProperty = 'voodoo';
		Assert::same(TRUE, isset($this->object->stringProperty));
		unset($this->object->stringProperty);
		Assert::same(FALSE, isset($this->object->stringProperty));
	}

	/**
	 * @testCase
	 */
	public function testPropertyNoTypeSetter()
	{
		$this->object->mixed = 'voodoo';
		Assert::same('voodoo', $this->object->mixed);
		$this->object->mixed = 666;
		Assert::same(666, $this->object->mixed);
		$dateTime = new DateTime();
		$this->object->mixed = $dateTime;
		Assert::same($dateTime, $this->object->mixed);
	}

	/**
	 * @testCase
	 */
	public function testPropertyScalarTypesSetter()
	{
		Assert::error(function() {
			$this->object->stringsProperty = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'string[]\', but actually is \'string\'.');
		$this->object->stringsProperty = array('voodoo', 'boo');
		Assert::same(array('voodoo', 'boo'), $this->object->stringsProperty);
		$this->object->stringsProperty = array('boo', 'voodoo');
		Assert::notSame(array('voodoo', 'boo'), $this->object->stringsProperty);
		$this->object->stringsProperty = array('hallo' => 'boo', 'goodbye' => 'voodoo');
		Assert::notSame(array('boo', 'voodoo'), $this->object->stringsProperty);
		Assert::error(function() {
			$this->object->stringsProperty = array('voodoo', 666);
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'string[]\', but actually is \'array\'.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyObjectTypesSetter()
	{
		Assert::error(function() {
			$this->object->dateTimes = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set value. Value is supposed to be \'DateTime[]\', but actually is \'string\'.');
		$dates = array(new DateTime(1000), new DateTime(2000));
		$this->object->dateTimes = $dates;
		Assert::same($dates, $this->object->dateTimes);
	}
}

class AbstractObjectExtendedTest extends AbstractObjectTest
{
	public function setUp()
	{
		$this->object = new ObjectExtended();
	}

	/**
	 * @testCase
	 */
	public function testExtendedProperty()
	{
		$this->object->isExtended = true;
		Assert::same(true, $this->object->isExtended);
	}
}

$abstractObjectTest = new AbstractObjectTest();
$abstractObjectTest->run();

$abstractObjectExtendedTest = new AbstractObjectExtendedTest();
$abstractObjectExtendedTest->run();