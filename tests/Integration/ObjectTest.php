<?php

namespace Tests\Integration;

use Aternos\Rados\Constants\ChecksumType;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Generated\Errno;
use Tests\RadosTestCase;

class ObjectTest extends RadosTestCase
{
    public function testObjectReadWrite(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-object");
        $object->writeFull("test-data");
        $this->assertEquals("test-data", $object->read(100, 0));

        $this->assertTrue($object->compareExt("test-data", 0));
        $this->assertEquals(4, $object->compareExt("test_data", 0));

        $object->write("da7a", 5);
        $this->assertEquals("test-da7a", $object->read(100, 0));
        $object->append("-test");
        $this->assertEquals("test-da7a-test", $object->read(100, 0));
        $this->assertEquals("test-da7a", $object->read(9, 0));
        $object->truncate(9);
        $this->assertEquals("test-da7a", $object->read(100, 0));
    }

    public function testRemoveObject(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-rm-object");
        $object->writeFull("test-data");
        $this->assertEquals("test-data", $object->read(100, 0));
        $object->remove();

        $this->expectExceptionCode(-Errno::ENOENT->value);
        $object->read(100, 0);
    }

    public function testChecksums(): void
    {
        $data = substr(str_repeat("testTESTtest", 1024), 0, 1024 * 10);
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-checksums-object");
        $object->writeFull($data);

        $crc = $object->checksum(ChecksumType::Crc32c, 0, 1024*10, 0);
        $this->assertEquals([793870968], $crc);

        $xxh32 = $object->checksum(ChecksumType::XXHash32, 0, 1024*10, 0);
        $this->assertEquals([96778833], $xxh32);

        $xxh64 = $object->checksum(ChecksumType::XXHash64, 0, 1024*10, 0);
        $this->assertEquals([5669559742893951849], $xxh64);
    }

    public function testAsyncReadWrite(): void
    {
        $data = substr(str_repeat("testTESTtest", 1024), 0, 1024 * 10);
        $ioContext = $this->getIOContext();
        $object1 = $ioContext->getObject("test-async-write-1");
        $object2 = $ioContext->getObject("test-async-write-2");

        $compl1 = $object1->writeFullAsync($data);
        $compl2 = $object2->writeFullAsync($data);

        $compl1->waitForComplete();
        $this->assertTrue($compl1->isComplete());
        $compl2->waitForComplete();
        $this->assertTrue($compl2->isComplete());

        $this->assertNull($compl1->getResult());
        $this->assertNull($compl2->getResult());

        $compl1 = $object1->readAsync(1024*10, 0);
        $compl2 = $object2->readAsync(1024*10, 0);

        // wait for completion
        while (!$compl1->isComplete() || !$compl2->isComplete()) {
            usleep(100000);
        }
        $this->assertTrue($compl1->isComplete());
        $this->assertTrue($compl2->isComplete());

        $this->assertEquals($data, $compl1->getResult());
        $this->assertEquals($data, $compl2->getResult());
    }

    public function testXAttributes(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-xattr-object");
        $object->writeFull("test-data");

        $object->setXAttribute("test-xattr", "test-value");
        $this->assertEquals("test-value", $object->getXAttribute("test-xattr"));

        $object->setXAttribute("test-xattr2", "test-value2");
        $object->setXAttribute("test-xattr3", "test-value3");

        $attributes = iterator_to_array($object->getXAttributes());
        $this->assertEquals([
            "test-xattr" => "test-value",
            "test-xattr2" => "test-value2",
            "test-xattr3" => "test-value3"
        ], $attributes);

        $object->removeXAttribute("test-xattr");

        $this->expectExceptionCode(-Errno::ENODATA->value);
        $object->getXAttribute("test-xattr");
    }

    public function testObjectStat(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-stat");
        $object->writeFull("test-data");

        $stat = $object->stat();
        $this->assertEquals(9, $stat->getSize());
        $this->assertGreaterThan(time() - 10, $stat->getModifiedTime()->getSeconds());
        $this->assertLessThanOrEqual(time(), $stat->getModifiedTime()->getSeconds());
    }

    public function testObjectStatAsync(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-stat-async");
        $object->writeFull("test-data");

        $stat = $object->statAsync()->waitAndGetResult();
        $this->assertEquals(9, $stat->getSize());
        $this->assertGreaterThan(time() - 10, $stat->getModifiedTime()->getSeconds());
        $this->assertLessThanOrEqual(time(), $stat->getModifiedTime()->getSeconds());
    }
}
