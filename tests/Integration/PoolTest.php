<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Cluster;
use Tests\RadosTestCase;

class PoolTest extends RadosTestCase
{
    public function testGetName(): void
    {
        $pool = $this->getPool();
        $this->assertIsString($pool->getName());
    }

    public function testGetId(): void
    {
        $pool = $this->getPool();
        $this->assertIsInt($pool->getId());
    }

    public function testGetCluster(): void
    {
        $pool = $this->getPool();
        $this->assertInstanceOf(Cluster::class, $pool->getCluster());
    }

    public function testGetBaseTier(): void
    {
        $pool = $this->getPool();
        $this->assertIsInt($pool->getBaseTier());
    }

    public function testListInconsistentPgs(): void
    {
        $pool = $this->getPool();
        $this->assertIsArray($pool->listInconsistentPgs());
    }
}
