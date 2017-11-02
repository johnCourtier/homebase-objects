<?php

namespace Foo {
	class Bar {}
}

namespace {

use Homebase\Model\PropertyContainer;
use Tester\Assert;
use Tester\TestCase;
use Foo\Bar as FooBar;

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
 * @property date $myDate
 * @property Foo\Bar $fooBar
 * @property DateTime $lazy
 */
class NewPropertyContainer extends PropertyContainer
{
	protected function createProperty(array $propertyQualities)
	{
		if ($propertyQualities['name'] === 'lazy') {
			return Homebase\Model\LazyProperty::createProperty(
				$propertyQualities['name'],
				isset($propertyQualities['access']) ? $propertyQualities['access'] :null,
				isset($propertyQualities['type']) ? $propertyQualities['type'] :null,
				isset($propertyQualities['description']) ? $propertyQualities['description'] :null
			);
		}

		return parent::createProperty($propertyQualities);
	}
}

/**
 * @property bool $isExtended
 */
class NewPropertyContainerExtended extends NewPropertyContainer
{

}

class PropertyContainerTest extends TestCase
{
	/** @var NewPropertyContainer */
	protected $propertyContainer;

	public function setUp()
	{
		Assert::noError(function() {
			$this->propertyContainer = new NewPropertyContainer();
		});
	}

	/**
	 * @testCase
	 */
	public function testPropertyExistence()
	{
		$this->propertyContainer->stringProperty = 'voodoo';
		Assert::error(function() {
			return $this->propertyContainer->nonProperty;
		}, 'E_USER_ERROR', 'Unable to get property \'nonProperty\'. No such property is defined in \''.get_class($this->propertyContainer).'\'. Use \''.get_class($this->propertyContainer).'::propertyExists\' method to avoid this error.');
		Assert::error(function() {
			return isset($this->propertyContainer->nonProperty);
		}, 'E_USER_WARNING', 'Class \''.get_class($this->propertyContainer).'\' has no \'nonProperty\' property defined. Use \''.get_class($this->propertyContainer).'::propertyExists\' method to avoid this warning.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyAccessWriteOnly()
	{
		Assert::same(TRUE, $this->propertyContainer->isPropertyWriteable('stringPropertyWrite'));
		Assert::same(FALSE, $this->propertyContainer->isPropertyReadable('stringPropertyWrite'));
		Assert::error(function() {
			return $this->propertyContainer->stringPropertyWrite;
		}, 'E_USER_ERROR', 'Unable to get property \'stringPropertyWrite\'. No such property is readable in \''.get_class($this->propertyContainer).'\' class. Use \''.get_class($this->propertyContainer).'::isPropertyReadable\' method to avoid this error.');
		$this->propertyContainer->stringPropertyWrite = 'voodoo';
	}

	/**
	 * @testCase
	 */
	public function testPropertyAccessReadOnly()
	{
		Assert::same(FALSE, $this->propertyContainer->isPropertyWriteable('stringPropertyRead'));
		Assert::same(TRUE, $this->propertyContainer->isPropertyReadable('stringPropertyRead'));
		Assert::error(function() {
			return $this->propertyContainer->stringPropertyRead;
		}, 'E_USER_ERROR', 'Unable to get property \'stringPropertyRead\'. No such property was yet set in \''.get_class($this->propertyContainer).'\' class.');
		Assert::error(function() {
			$this->propertyContainer->stringPropertyRead = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set property \'stringPropertyRead\'. No such property is writeable in \''.get_class($this->propertyContainer).'\' class. Use \''.get_class($this->propertyContainer).'::isPropertyWriteable\' method to avoid this error.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyCombinedAccess()
	{
		Assert::same(TRUE, $this->propertyContainer->isPropertyWriteable('stringProperty'));
		Assert::same(TRUE, $this->propertyContainer->isPropertyReadable('stringProperty'));
		Assert::error(function() {
			return $this->propertyContainer->stringProperty;
		}, 'E_USER_ERROR', 'Unable to get property \'stringProperty\'. No such property was yet set in \''.get_class($this->propertyContainer).'\' class.');
		$this->propertyContainer->stringProperty = 'voodoo';
		Assert::same('voodoo', $this->propertyContainer->stringProperty);
	}

	/**
	 * @testCase
	 */
	public function testPropertyScalarTypeSetter()
	{
		Assert::error(function() {
			$this->propertyContainer->stringProperty = 666;
		}, 'E_USER_ERROR', 'Unable to set value of \'stringProperty\' property. Value is supposed to be \'string\', but actually is \'integer\'.');
		Assert::error(function() {
			$this->propertyContainer->stringProperty = null;
		}, 'E_USER_ERROR', 'Unable to set value of \'stringProperty\' property. Value is supposed to be \'string\', but actually is \'NULL\'.');
		$this->propertyContainer->stringProperty = 'voodoo';
		Assert::same('voodoo', $this->propertyContainer->stringProperty);

		Assert::error(function() {
			$this->propertyContainer->intProperty = '666';
		}, 'E_USER_ERROR', 'Unable to set value of \'intProperty\' property. Value is supposed to be \'int\', but actually is \'string\'.');
		Assert::error(function() {
			$this->propertyContainer->intProperty = null;
		}, 'E_USER_ERROR', 'Unable to set value of \'intProperty\' property. Value is supposed to be \'int\', but actually is \'NULL\'.');
		$this->propertyContainer->intProperty = 666;

		$this->propertyContainer->numericProperty = 666;
		$this->propertyContainer->numericProperty = '666';
		Assert::error(function() {
			$this->propertyContainer->numericProperty = null;
		}, 'E_USER_ERROR', 'Unable to set value of \'numericProperty\' property. Value is supposed to be \'numeric\', but actually is \'NULL\'.');

		$this->propertyContainer->null = null;
		Assert::error(function() {
			$this->propertyContainer->null = 'null';
		}, 'E_USER_ERROR', 'Unable to set value of \'null\' property. Value is supposed to be \'null\', but actually is \'string\'.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyCombinedTypeSetter()
	{
		Assert::error(function() {
			$this->propertyContainer->stringNullProperty = 666;
		}, 'E_USER_ERROR', 'Unable to set value of \'stringNullProperty\' property. Value is supposed to be \'string\' or \'null\', but actually is \'integer\'.');
		$this->propertyContainer->stringNullProperty = '666';
		$this->propertyContainer->stringNullProperty = null;
	}

	/**
	 * @testCase
	 */
	public function testPropertyObjectTypeSetter()
	{
		Assert::error(function() {
			$this->propertyContainer->dateTime = date('Y-m-d H:i:s');
		}, 'E_USER_ERROR', 'Unable to set value of \'dateTime\' property. Value is supposed to be \'DateTime\', but actually is \'string\'.');
		$this->propertyContainer->dateTime = new DateTime();
		$this->propertyContainer->fooBar = new FooBar();
	}

	/**
	 * @testCase
	 */
	public function testPropertyIssetUnset()
	{
		Assert::same(FALSE, isset($this->propertyContainer->stringProperty));
		$this->propertyContainer->stringProperty = 'voodoo';
		Assert::same(TRUE, isset($this->propertyContainer->stringProperty));
		unset($this->propertyContainer->stringProperty);
		Assert::same(FALSE, isset($this->propertyContainer->stringProperty));
	}

	/**
	 * @testCase
	 */
	public function testPropertyNoTypeSetter()
	{
		$this->propertyContainer->mixed = 'voodoo';
		Assert::same('voodoo', $this->propertyContainer->mixed);
		$this->propertyContainer->mixed = 666;
		Assert::same(666, $this->propertyContainer->mixed);
		$dateTime = new DateTime();
		$this->propertyContainer->mixed = $dateTime;
		Assert::same($dateTime, $this->propertyContainer->mixed);
	}

	/**
	 * @testCase
	 */
	public function testPropertyScalarTypesSetter()
	{
		Assert::error(function() {
			$this->propertyContainer->stringsProperty = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set value of \'stringsProperty\' property. Value is supposed to be \'string[]\', but actually is \'string\'.');
		$this->propertyContainer->stringsProperty = array('voodoo', 'boo');
		Assert::same(array('voodoo', 'boo'), $this->propertyContainer->stringsProperty);
		$this->propertyContainer->stringsProperty = array('boo', 'voodoo');
		Assert::notSame(array('voodoo', 'boo'), $this->propertyContainer->stringsProperty);
		$this->propertyContainer->stringsProperty = array('hallo' => 'boo', 'goodbye' => 'voodoo');
		Assert::notSame(array('boo', 'voodoo'), $this->propertyContainer->stringsProperty);
		Assert::error(function() {
			$this->propertyContainer->stringsProperty = array('voodoo', 666);
		}, 'E_USER_ERROR', 'Unable to set value of \'stringsProperty\' property. Value is supposed to be \'string[]\', but actually is \'array\'.');
	}

	/**
	 * @testCase
	 */
	public function testPropertyObjectTypesSetter()
	{
		Assert::error(function() {
			$this->propertyContainer->dateTimes = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set value of \'dateTimes\' property. Value is supposed to be \'DateTime[]\', but actually is \'string\'.');
		$dates = array(new DateTime(1000), new DateTime(2000));
		$this->propertyContainer->dateTimes = $dates;
		Assert::same($dates, $this->propertyContainer->dateTimes);
	}

	/**
	 * @testCase
	 */
	public function testMyDateType()
	{
		$date = date('Y-m-d H:i:s');
		$this->propertyContainer->myDate = $date;
		Assert::same($date, $this->propertyContainer->myDate);
		Assert::error(function() {
			$this->propertyContainer->myDate = 'voodoo';
		}, 'E_USER_ERROR', 'Unable to set value of \'myDate\' property. Value is supposed to be \'date\', but actually is \'string\'.');
	}

	/**
	 * @testCase
	 */
	public function testLazyProperty()
	{
		$callback = new Homebase\Model\Callback(function($offset) {
			$date = new DateTime('1988-06-12 12:01:01');
			$date->modify($offset);
			return $date;
		}, array('+1day'));
		$this->propertyContainer->lazy = $callback;
		Assert::same('1988-06-13 12:01:01', $this->propertyContainer->lazy->format('Y-m-d H:i:s'));
		Assert::error(function() use($callback) {
			$this->propertyContainer->lazy = $callback;
		}, 'E_USER_ERROR', 'Unable to set value of \'lazy\' property. Value is supposed to be \'DateTime\', but actually is \'Homebase\Model\Callback\'.');
		$this->propertyContainer->lazy = new DateTime('1988-06-12 12:02:02');
		Assert::same('1988-06-12 12:02:02', $this->propertyContainer->lazy->format('Y-m-d H:i:s'));
	}
}

class PropertyContainerExtendedTest extends PropertyContainerTest
{
	public function setUp()
	{
		$this->propertyContainer = new NewPropertyContainerExtended();
	}

	/**
	 * @testCase
	 */
	public function testExtendedProperty()
	{
		$this->propertyContainer->isExtended = true;
		Assert::same(true, $this->propertyContainer->isExtended);
	}
}

/**
 * @param string
 * @return bool
 */
function is_date($date)
{
  return strtotime($date);
}

$propertyContainerTest = new PropertyContainerTest();
$propertyContainerTest->run();

$propertyContainerExtendedTest = new PropertyContainerExtendedTest();
$propertyContainerExtendedTest->run();
}