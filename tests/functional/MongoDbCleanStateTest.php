<?php

use Codeception\Lib\ModuleContainer;
use Codeception\TestCase;
use Codeception\Util\Stub;

class MongoDbCleanStateTest extends \Codeception\TestCase\Test
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;

    /** @var \MongoClient */
    private $mongoClient;

    protected function _after()
    {
    }

    private function createDatabaseWithCollection($database, $collection)
    {
        $mongoDb = $this->mongoClient->selectDB($database);
        $mongoDb->createCollection($collection);
    }

    private function assertDatabaseHasBeenDeleted($database)
    {
        $mongoDb = $this->mongoClient->selectDB($database);
        $this->assertEmpty($mongoDb->listCollections(), 'There must be no collection left after database deletion.');
    }

    public function testModuleDropDatabases()
    {
        $databases = ['test1', 'test2'];

        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = Stub::make('Codeception\Lib\ModuleContainer');
        $module = new \Codeception\Module\MongoDbCleanState($moduleContainer, ['databases' => $databases]);

        $this->mongoClient = $module->getMongoClient();

        foreach($databases as $database) {
            $this->createDatabaseWithCollection($database, 'testCollection');;
        }

        /** @var TestCase $test */
        $test = Stub::make('Codeception\TestCase');
        $module->_before($test);

        foreach($databases as $database) {
            $this->assertDatabaseHasBeenDeleted($database);
        }
    }

    public function testConfigFromServerKey()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = Stub::make('Codeception\Lib\ModuleContainer');
        $module = new \Codeception\Module\MongoDbCleanState($moduleContainer, ['server' => 'test-server']);

        $this->assertEquals('' . $module->server(), 'test-server');
    }

    public function testConfigFromCredentials()
    {
        $options = [
            'username' => 'username',
            'password' => 'password',
            'host' => 'host',
            'port' => 'port',
        ];

        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = Stub::make('Codeception\Lib\ModuleContainer');
        $module = new \Codeception\Module\MongoDbCleanState($moduleContainer, $options);

        $this->assertEquals('' . $module->server(), 'mongodb://username:password@host:port');
    }

    public function testConfigFromEnv()
    {
        putenv("MONGO_USERNAME=env_user");
        putenv("MONGO_PASSWORD=env_pass");
        putenv("MONGO_HOST=env_host");
        putenv("MONGO_PORT=env_port");

        $options = [
            'fromEnv' => true,
            'username' => 'MONGO_USERNAME',
            'password' => 'MONGO_PASSWORD',
            'host' => 'MONGO_HOST',
            'port' => 'MONGO_PORT',
        ];

        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = Stub::make('Codeception\Lib\ModuleContainer');
        $module = new \Codeception\Module\MongoDbCleanState($moduleContainer, $options);

        $this->assertEquals('' . $module->server(), 'mongodb://env_user:env_pass@env_host:env_port');
    }

    public function testModuleUseDatabasesFromEnv()
    {
        $database = 'test1';
        putenv("MONGO_DATABASE=$database");

        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = Stub::make('Codeception\Lib\ModuleContainer');
        $module = new \Codeception\Module\MongoDbCleanState(
            $moduleContainer,
            ['fromEnv' => true, 'databases' => ['MONGO_DATABASE']]
        );

        $this->mongoClient = $module->getMongoClient();

        $this->createDatabaseWithCollection($database, 'testCollection');;

        /** @var TestCase $test */
        $test = Stub::make('Codeception\TestCase');
        $module->_before($test);

        $this->assertDatabaseHasBeenDeleted($database);
    }
}
