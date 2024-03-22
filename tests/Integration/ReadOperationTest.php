<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\ObjectStat;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Constants\ChecksumType;
use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Constants\OMapComparisonOperator;
use Aternos\Rados\Constants\OperationTaskFlag;
use Aternos\Rados\Constants\XAttributeComparisonOperator;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Operation\Common\Task\AssertExistsTask;
use Aternos\Rados\Operation\Common\Task\AssertVersionTask;
use Aternos\Rados\Operation\Common\Task\CompareExtTask;
use Aternos\Rados\Operation\Common\Task\CompareXAttributeTask;
use Aternos\Rados\Operation\Common\Task\OMapCompareTask;
use Aternos\Rados\Operation\Read\ReadOperation;
use Aternos\Rados\Operation\Read\Task\ChecksumTask;
use Aternos\Rados\Operation\Read\Task\GetXAttributesTask;
use Aternos\Rados\Operation\Read\Task\OMapGetByKeysTask;
use Aternos\Rados\Operation\Read\Task\OMapGetKeysTask;
use Aternos\Rados\Operation\Read\Task\OMapGetTask;
use Aternos\Rados\Operation\Read\Task\ReadTask;
use Aternos\Rados\Operation\Read\Task\StatTask;
use Aternos\Rados\Operation\Write\Task\OMapSetTask;
use Tests\RadosTestCase;

class ReadOperationTest extends RadosTestCase
{
    /**
     * @return array{IOContext, ReadOperation, RadosObject}
     * @throws RadosException
     */
    protected function init(): array
    {
        $ioContext = $this->getIOContext();
        $operation = $this->getRados()->createReadOperation();
        $objectId = "read-op-" . uniqid();
        $object = $ioContext->getObject($objectId);
        return [$ioContext, $operation, $object];
    }

    public function testChecksum(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $data = substr(str_repeat("testTESTtest", 1024), 0, 1024 * 10);
        $object->writeFull($data);

        $crc = new ChecksumTask(ChecksumType::Crc32c, 0, 1024*10, 0);
        $xxh32 = new ChecksumTask(ChecksumType::XXHash32, 0, 1024*10, 0);
        $xxh64 = new ChecksumTask(ChecksumType::XXHash64, 0, 1024*10, 0);

        $operation->addTask($crc)->addTask($xxh32)->addTask($xxh64)->operate($object);

        $this->assertEquals([793870968], $crc->getResult());
        $this->assertEquals([96778833], $xxh32->getResult());
        $this->assertEquals([5669559742893951849], $xxh64->getResult());
    }

    public function testGetXAttributes(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $attributes = [
            "test" => "test-value",
            "test2" => "test-value2"
        ];
        foreach ($attributes as $key => $value) {
            $object->setXAttribute($key, $value);
        }

        $task = new GetXAttributesTask();
        $operation->addTask($task)->operate($object);
        $this->assertEquals($attributes, iterator_to_array($task->getResult()));
    }

    public function testOMapGet()
    {
        [$ioContext, $operation, $object] = $this->init();
        $omap = [
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3"
        ];
        $writeOp = $this->getRados()->createWriteOperation();
        $writeOp->addTask(new OMapSetTask($omap));
        $writeOp->operate($object);

        $noValues = [];
        foreach (array_keys($omap) as $key) {
            $noValues[$key] = null;
        }

        $byKeys = new OMapGetByKeysTask(array_keys($omap));
        $getKeys = new OMapGetKeysTask(100);
        $get = new OMapGetTask(100);

        $operation->addTask($byKeys)->addTask($getKeys)->addTask($get)->operate($object);
        $this->assertEquals($omap, iterator_to_array($byKeys->getResult()->getIterator()));
        $this->assertEquals($omap, iterator_to_array($get->getResult()->getIterator()));
        $this->assertEquals($noValues, iterator_to_array($getKeys->getResult()->getIterator()));
    }

    public function testRead(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test-data");

        $task = new ReadTask(100, 0);
        $operation->addTask($task)->operate($object);

        $this->assertEquals("test-data", $task->getResult());
    }

    public function testStat(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test-data");

        $task = new StatTask();
        $operation->addTask($task)->operate($object);
        $stat = $task->getResult();
        $this->assertInstanceOf(ObjectStat::class, $stat);
        $this->assertEquals(9, $stat->getSize());
        $this->assertGreaterThan(time() - 10, $stat->getModifiedTime()->getSeconds());
        $this->assertLessThanOrEqual(time(), $stat->getModifiedTime()->getSeconds());
    }
}
