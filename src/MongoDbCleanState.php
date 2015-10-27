<?php
namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\TestCase;

class MongoDbCleanState extends Module
{

    private $mongoClient;

    private $databases;

    /**
     * @param ModuleContainer $container
     * @param null $config
     */
    public function __construct(ModuleContainer $container, $config = null)
    {
        parent::__construct($container, $config);
    }

    /**
     * Get the databases
     */
    private function databases()
    {
        if (null === $this->databases) {
            $this->initializeDatabases();
        }

        return $this->databases;
    }

    private function initializeDatabases()
    {
        $this->databases = isset($this->config['databases']) ? $this->config['databases'] : [];

        if ($this->isFromEnv()) {
            foreach ($this->databases as $key => $database) {
                $this->databases[$key] = getenv($database);
            }
        }
    }

    /**
     * Are credentials references from environment variables?
     *
     * @return bool
     */
    private function isFromEnv()
    {
        return isset($this->config['fromEnv']) && $this->config['fromEnv'];
    }

    /**
     * Create the server string.
     * It can be a server key or a combinaison of username, password, host and port.
     * If there is a fromEnv key which is true, then the username, password, host
     * and port keys references environment variables' name.
     *
     * @return string
     */
    public function server()
    {
        if (isset($this->config['server'])) {
            return $this->config['server'];
        }

        return $this->isFromEnv() ? $this->serverFromEnv() : $this->serverFromConfig();
    }

    /**
     * Create the server string from environment variables.
     *
     * @return string
     */
    private function serverFromEnv()
    {
        $server = 'mongodb://';

        if (isset($this->config['username'], $this->config['password'])) {
            $server .= getenv($this->config['username']) . ':' . getenv($this->config['password']) . '@';
        }

        $server .= isset($this->config['host']) ? getenv($this->config['host']) : 'localhost';
        $server .= ':' . (isset($this->config['port']) ? getenv($this->config['port']) : '27017');

        return $server;
    }

    /**
     * Create the server string from config variables.
     *
     * @return string
     */
    private function serverFromConfig()
    {
        $server = 'mongodb://';

        if (isset($this->config['username'], $this->config['password'])) {
            $server .= $this->config['username'] . ':' . $this->config['password'] . '@';
        }

        $server .= isset($this->config['host']) ? $this->config['host'] : 'localhost';
        $server .= ':' . (isset($this->config['port']) ? $this->config['port'] : '27017');

        return $server;
    }

    /**
     * Get the MongoClient
     *
     * @return \MongoClient
     */
    public function getMongoClient()
    {
        if (null === $this->mongoClient) {
            $this->instantiateMongoClient();
        }

        return $this->mongoClient;
    }

    /**
     * Instantiate the MongoClient
     */
    private function instantiateMongoClient()
    {
        $server = $this->server();
        $options = isset($this->config['options']) ? $this->config['options'] : ['connect' => true, 'fsync' => true];
        $driver_options = isset($this->config['driver_options']) ? $this->config['driver_options'] : [];

        $this->mongoClient = new \MongoClient($server, $options, $driver_options);
    }

    /**
     * Drop the databases before each tests
     *
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        foreach ($this->databases() as $database) {
            $mongoDb = $this->getMongoClient()->selectDB($database);
            $mongoDb->drop();
        }
    }
}
