<?php

namespace Tests;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Cluster\Pool\Pool;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Rados;
use Exception;
use PHPUnit\Framework\TestCase;

abstract class RadosTestCase extends TestCase
{
    protected static ?Rados $rados = null;
    protected static ?Cluster $cluster = null;
    protected static array $pools = [];

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (isset(static::$pools[static::class])) {
            try {
                static::$pools[static::class]->delete();
            } catch (Exception) {
            }
        }
    }

    /**
     * @return Rados
     */
    public function getRados(): Rados
    {
        if (static::$rados === null) {
            static::$rados = Rados::getInstance()->initialize();
        }
        return static::$rados;
    }

    /**
     * @return Cluster
     * @throws RadosException
     */
    public function getCluster(): Cluster
    {
        if (static::$cluster === null) {
            static::$cluster = $this->getRados()->createCluster()->configReadFile(null)->connect();
        }
        return static::$cluster;
    }

    /**
     * @return Pool
     * @throws RadosException
     */
    public function getPool(): Pool
    {
        if (!isset(static::$pools[static::class])) {
            static::$pools[static::class] = $this->getCluster()->createPool("ffi-test-" . uniqid());
        }
        return static::$pools[static::class];
    }
}
