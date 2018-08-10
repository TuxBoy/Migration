<?php
namespace UnitTest\Migration;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use SDAM\Config;
use SDAM\Maintainer;
use UnitTest\Fixtures\FakeEntity;
use UnitTest\Fixtures\Post;
use UnitTest\Fixtures\Simple;

class MaintainerTest extends TestCase
{

	public function setUp()
	{
		parent::setUp();
		Config::current()->configure([
			Config::DATABASE    => ['url' => 'sqlite:///:memory:'],
			Config::ENTITY_PATH => 'UnitTest\Fixtures'
		]);
	}

	/**
	 * @param string[]|string $entities
	 * @return array [Maintainer, SchemaManager]
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 * @throws \Throwable
	 */
	private function makeMaintainer($entities): array
	{
		$entities   = is_string($entities) ? [$entities] : $entities;
		$maintainer = new Maintainer($entities);
		$maintainer->run();

		return [$maintainer, $maintainer->connection->getSchemaManager()];
	}

	public function testMaintainerConstruct()
    {
        [$maintainer,] = $this->makeMaintainer(FakeEntity::class);
		self::assertCount(1, $maintainer->entities);
    }

    public function testAddPrimaryKey()
	{
		[, $schemaManager] = $this->makeMaintainer(FakeEntity::class);
		$columns = $schemaManager->listTableColumns('fakes');
		self::assertTrue(array_key_exists('id', $columns));
	}

    public function testAddSimpleColumn()
	{
		[, $schemaManager] = $this->makeMaintainer(Simple::class);
		$columns = $schemaManager->listTableColumns('simples');
		self::assertTrue(array_key_exists('name', $columns));

		self::assertEquals('string', $columns['name']->getType()->getName());
	}

    public function testAddBooleanColumn()
	{
		[, $schemaManager] = $this->makeMaintainer(FakeEntity::class);
		$columns = $schemaManager->listTableColumns('fakes');
		self::assertTrue(array_key_exists('online', $columns));

		self::assertEquals(Type::BOOLEAN, $columns['online']->getType()->getName());
	}

	public function testAddBelongsToRelation()
	{
		/** @var $schemaManager AbstractSchemaManager */
		[, $schemaManager] = $this->makeMaintainer(FakeEntity::class);
		$columns = $schemaManager->listTableColumns('fakes');
		self::assertArrayHasKey('simple_id', $columns);
	}

	public function testAddBelongsToManyRelation()
	{
		/** @var $schemaManager AbstractSchemaManager */
		[, $schemaManager] = $this->makeMaintainer([FakeEntity::class]);
		self::assertTrue($schemaManager->tablesExist('fakes_posts'));
		$columns = $schemaManager->listTableColumns('fakes_posts');
		self::assertArrayHasKey('post_id', $columns);
		self::assertArrayHasKey('fake_id', $columns);
	}

	public function testAddTimestampFieldWithDefaultValue()
	{
		/** @var $schemaManager AbstractSchemaManager */
		[, $schemaManager] = $this->makeMaintainer(Simple::class);
		$columns = $schemaManager->listTableColumns('simples');
		self::assertArrayHasKey('created_at', $columns);

		self::assertEquals(date('Y-m-d H:i:s'), $columns['created_at']->getDefault());
	}

}