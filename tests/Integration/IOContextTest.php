<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Cluster\ClusterConfig;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectCursor;
use Aternos\Rados\Cluster\Pool\PoolStat;
use Tests\RadosTestCase;

class IOContextTest extends RadosTestCase
{
    public function testGetConfigHandle(): void
    {
        $configHandle = $this->getIOContext()->getConfigHandle();
        $this->assertInstanceOf(ClusterConfig::class, $configHandle);
    }

    public function testGetCluster(): void
    {
        $this->assertInstanceOf(Cluster::class, $this->getIOContext()->getCluster());
    }

    public function testGetPoolStat(): void
    {
        $stat = $this->getIOContext()->poolStat();
        $this->assertInstanceOf(PoolStat::class, $stat);
        $this->assertGreaterThanOrEqual(0, $stat->getNumBytes());
        $this->assertGreaterThanOrEqual(0, $stat->getNumObjects());
        $this->assertGreaterThanOrEqual(0, $stat->getCompressedBytesOrig());
        $this->assertGreaterThanOrEqual(0, $stat->getNumObjectsDegraded());
        $this->assertGreaterThanOrEqual(0, $stat->getNumObjectCopies());
    }

    public function testPoolRequiresAlignment(): void
    {
        $this->assertIsBool($this->getIOContext()->getPoolRequiresAlignment());
    }

    public function testPoolRequiredAlignment(): void
    {
        $this->assertIsInt($this->getIOContext()->getPoolRequiredAlignment());
    }

    public function testSetGetNamespace(): void
    {
        $namespace = "ffi-test-" . uniqid();
        $this->getIOContext()->setNamespace($namespace);
        $this->assertEquals($namespace, $this->getIOContext()->getNamespace());
        $this->getIOContext()->setNamespace(null);
    }

    public function testGetLastVersion(): void
    {
        $this->assertIsInt($this->getIOContext()->getLastVersion());
    }

    public function testGetCursor(): void
    {
        $this->assertInstanceOf(ObjectCursor::class, $this->getIOContext()->getCursorAtBeginning());
        $this->assertInstanceOf(ObjectCursor::class, $this->getIOContext()->getCursorAtEnd());
    }

    public function testListEnabledApplications(): void
    {
        $this->assertIsArray($this->getIOContext()->listEnabledApplications());
    }

    public function testObjectIterator(): void
    {
        $objectNames = ["it-test-1", "it-test-2", "it-test-3", "it-test-4", "it-test-5"];
        foreach ($objectNames as $object) {
            $this->getIOContext()->getObject($object)->writeFull("test");
        }

        $objects = [];
        $test2Cursor = null;
        $iterator = $this->getIOContext()->createObjectIterator();
        foreach ($iterator as $cursor => $object) {
            if ($object->getEntry() === "it-test-2") {
                $test2Cursor = $cursor;
            }
            $objects[] = $object->getEntry();
        }

        $this->assertTrue(!array_diff($objectNames, $objects));

        // test rewind
        $objects2 = [];
        foreach ($iterator as $object) {
            $objects2[] = $object->getEntry();
        }

        $this->assertEquals($objects, $objects2);

        $iterator->seekCursor($test2Cursor);
        $this->assertEquals($iterator->current()->getEntry(), "it-test-2");
    }
}
