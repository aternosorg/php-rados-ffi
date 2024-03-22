<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Constants\CreateMode;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Operation\Common\Task\AssertExistsTask;
use Aternos\Rados\Operation\Read\Task\OMapGetTask;
use Aternos\Rados\Operation\Write\Task\AppendTask;
use Aternos\Rados\Operation\Write\Task\CreateObjectTask;
use Aternos\Rados\Operation\Write\Task\OMapClearTask;
use Aternos\Rados\Operation\Write\Task\OMapRemoveKeyRangeTask;
use Aternos\Rados\Operation\Write\Task\OMapRemoveKeysTask;
use Aternos\Rados\Operation\Write\Task\OMapSetTask;
use Aternos\Rados\Operation\Write\Task\RemoveTask;
use Aternos\Rados\Operation\Write\Task\RemoveXAttributeTask;
use Aternos\Rados\Operation\Write\Task\SetXAttributeTask;
use Aternos\Rados\Operation\Write\Task\TruncateTask;
use Aternos\Rados\Operation\Write\Task\WriteFullTask;
use Aternos\Rados\Operation\Write\Task\WriteSameTask;
use Aternos\Rados\Operation\Write\Task\WriteTask;
use Aternos\Rados\Operation\Write\Task\ZeroTask;
use Aternos\Rados\Operation\Write\WriteOperation;
use Tests\RadosTestCase;

class WriteOperationTest extends RadosTestCase
{
    /**
     * @return array{IOContext, WriteOperation, RadosObject}
     * @throws RadosException
     */
    protected function init(): array
    {
        $ioContext = $this->getIOContext();
        $operation = $this->getRados()->createWriteOperation();
        $objectId = "read-op-" . uniqid();
        $object = $ioContext->getObject($objectId);
        return [$ioContext, $operation, $object];
    }

    public function testWrite(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $writeFull = new WriteFullTask("test-test-test");
        $writeSame = new WriteSameTask("-test", 10, 14);
        $write = new WriteTask("-test", 24);
        $append = new AppendTask("-test");

        $operation->addTask($writeFull)->addTask($writeSame)->addTask($write)->addTask($append)->operate($object);
        $this->assertEquals("test-test-test-test-test-test-test", $object->read(100, 0));
    }

    public function testCreateObject(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $operation->addTask(new CreateObjectTask(CreateMode::Exclusive))
            ->addTask($task = new AssertExistsTask())
            ->operate($object);

        $this->assertNull($task->getResult());
    }

    public function testOMapWrite(): void
    {
        [$ioContext, $operation, $object] = $this->init();

        $omap = [
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3",
            "key4" => "value4",
            "key5" => "value5",
            "key6" => "value6",
            "key7" => "value7",
            "key8" => "value8",
            "key9" => "value9"
        ];

        $set = new OMapSetTask($omap);
        $remove = new OMapRemoveKeysTask(["key9"]);
        $removeRange = new OMapRemoveKeyRangeTask("key5", "key7");
        $operation->addTask($set)->addTask($remove)->addTask($removeRange)->operate($object);

        $read = $this->getRados()->createReadOperation();
        $task = new OMapGetTask(100);
        $read->addTask($task)->operate($object);

        $this->assertEquals([
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3",
            "key4" => "value4",
            "key7" => "value7",
            "key8" => "value8"
        ], iterator_to_array($task->getResult()->getIterator()));

        $operation = $this->getRados()->createWriteOperation();
        $operation->addTask(new OMapClearTask())->operate($object);

        $read = $this->getRados()->createReadOperation();
        $task = new OMapGetTask(100);
        $read->addTask($task)->operate($object);

        $this->assertEquals([], iterator_to_array($task->getResult()->getIterator()));
    }

    public function testRemove(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");

        $operation->addTask(new RemoveTask())->operate($object);

        $operation = $this->getRados()->createReadOperation();
        $operation->addTask(new AssertExistsTask());
        $this->expectExceptionCode(-Errno::ENOENT->value);
        $operation->operate($object);
    }

    public function testXAttributes(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");
        $operation
            ->addTask(new SetXAttributeTask("test", "test"))
            ->addTask(new SetXAttributeTask("test2", "test2"))
            ->operate($object);

        $operation = $this->getRados()->createWriteOperation();
        $operation
            ->addTask(new RemoveXAttributeTask("test"))
            ->operate($object);

        $this->assertEquals(["test2" => "test2"], iterator_to_array($object->getXAttributes()));
    }

    public function testTruncate(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("testtest");

        $operation->addTask(new TruncateTask(4))->operate($object);
        $this->assertEquals("test", $object->read(100, 0));
    }

    public function testZero(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("testtest");

        $operation->addTask(new ZeroTask(2, 2))->operate($object);
        $this->assertEquals("te\0\0test", $object->read(100, 0));
    }
}
