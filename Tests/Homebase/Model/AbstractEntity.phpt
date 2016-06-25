<?php

use Homebase\Model\AbstractEntity;
use Tester\Assert;
use Tester\TestCase;

require substr(__DIR__, 0, strpos(__DIR__, 'Tests')+5) . '/../vendor/autoload.php';

/**
 * @property string $property
 */
class Entity extends AbstractEntity
{
}

class AbstractEntityTest extends TestCase
{
	/** @var Entity */
	protected $entity;

	public function setUp()
	{
		Assert::noError(function() {
			$this->entity = new Entity();
		});
	}

	/**
	 * @testCase
	 */
	public function testEntityPropertyIsChanged()
	{
		Assert::error(function() {
			return $this->entity->isChanged('nonProperty');
		}, 'E_USER_WARNING', 'Class \''.get_class($this->entity).'\' has no \'nonProperty\' property defined.');
		Assert::same(false, $this->entity->isChanged('property'));
		$this->entity->property = '666';
		Assert::same(false, $this->entity->isChanged('property'));
		$this->entity->property = 'voodoo';
		Assert::same(true, $this->entity->isChanged('property'));
		$this->entity->property = '666';
		Assert::same(false, $this->entity->isChanged('property'));
	}
}

$abstractEntityTest = new AbstractEntityTest();
$abstractEntityTest->run();