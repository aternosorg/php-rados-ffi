<?php

namespace Tests\Integration;

use Aternos\Rados\Constants\ChecksumType;
use Tests\RadosTestCase;

class ObjectTest extends RadosTestCase
{
    public function testObjectReadWrite(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-object");
        $object->writeFull("test-data");
        $this->assertEquals("test-data", $object->read(100, 0));
        $object->write("da7a", 5);
        $this->assertEquals("test-da7a", $object->read(100, 0));
        $object->append("-test");
        $this->assertEquals("test-da7a-test", $object->read(100, 0));
        $this->assertEquals("test-da7a", $object->read(9, 0));
        $object->truncate(9);
        $this->assertEquals("test-da7a", $object->read(100, 0));
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
}
