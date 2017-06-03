<?php

use Homebase\Model\Entity;
use Tester\Assert;
use Tester\TestCase;

require substr(__DIR__, 0, strpos(__DIR__, 'Tests')+5) . '/../vendor/autoload.php';

/**
 * @property string $property
 */
class NewEntity extends Entity
{
}

class EntityTest extends TestCase
{
	/** @var Entity */
	protected $entity;

	public function setUp()
	{
		Assert::noError(function() {
			$this->entity = new NewEntity();
		});
	}

	/**
	 * @testCase
	 */
	public function testEntityPropertyIsChanged()
	{
		Assert::error(function() {
			return $this->entity->isPropertyChanged('nonProperty');
		}, 'E_USER_WARNING', 'Class \''.get_class($this->entity).'\' has no \'nonProperty\' property defined. Use \''.get_class($this->entity).'::propertyExists\' method to avoid this warning.');
		Assert::same(false, $this->entity->isPropertyChanged('property'));
		$this->entity->property = '666';
		Assert::same(false, $this->entity->isPropertyChanged('property'));
		$this->entity->property = 'voodoo';
		Assert::same(true, $this->entity->isPropertyChanged('property'));
		$this->entity->property = '666';
		Assert::same(false, $this->entity->isPropertyChanged('property'));
	}
}

$entityTest = new EntityTest();
$entityTest->run();