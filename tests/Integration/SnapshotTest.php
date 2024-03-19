<?php

namespace Tests\Integration;

use Tests\RadosTestCase;

class SnapshotTest extends RadosTestCase
{
    public function testSnapshots(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-object");
        $object->writeFull("test-data-1");

        $snapshotName = "test-snap-" . uniqid();
        $snapshot = $ioContext->createSnapshot($snapshotName);
        $list = $ioContext->listSnapshots();
        $this->assertIsArray($list);
        $this->assertEquals(1, count($list));
        $this->assertEquals($snapshotName, $list[0]->getName());

        $object->writeFull("test-data-2");
        $this->assertEquals("test-data-2", $object->read(11, 0));
        $ioContext->setReadSnapshot($snapshot);
        $this->assertEquals("test-data-1", $object->read(11, 0));
        $ioContext->setReadSnapshot(null);
        $object->rollback($snapshot);
        $this->assertEquals("test-data-1", $object->read(11, 0));
        $snapshot->remove();
    }
}
