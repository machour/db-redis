<?php

namespace yii\db\redis\tests;

use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;
use yii\redis\Connection;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \yii\tests\TestCase
{
    public static $params;


    /**
     * Returns a test configuration param from /data/config.php
     * @param string $name params name
     * @param mixed $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(__DIR__ . '/data/config.php');
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }

    protected function setUp()
    {
        $databases = self::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : null;
        if ($params === null) {
            $this->markTestSkipped('No redis server connection configured.');
        }
        $connection = new Connection($params);
//        if (!@stream_socket_client($connection->hostname . ':' . $connection->port, $errorNumber, $errorDescription, 0.5)) {
//            $this->markTestSkipped('No redis server running at ' . $connection->hostname . ':' . $connection->port . ' : ' . $errorNumber . ' - ' . $errorDescription);
//        }

        $this->mockApplication();
        $this->container->set('redis', $connection);

        parent::setUp();
    }

    /**
     * @param boolean $reset whether to clean up the test database
     * @return Connection
     */
    public function getConnection($reset = true)
    {
        $databases = self::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : [];
        $db = new Connection($params);
        if ($reset) {
            $db->open();
            $db->flushdb();
        }

        return $db;
    }

    /**
     * Invokes a inaccessible method.
     * @param $object
     * @param $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution
     * @return mixed
     */
    protected function invokeMethod($object, $method, $args = [], $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        if ($revoke) {
            $method->setAccessible(false);
        }

        return $result;
    }
}
