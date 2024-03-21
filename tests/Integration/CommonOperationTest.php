<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;
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
use Aternos\Rados\Operation\Write\Task\OMapSetTask;
use Tests\RadosTestCase;

class CommonOperationTest extends RadosTestCase
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

    public function testAssertExists(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");
        $operation->addTask(new AssertExistsTask());
        $operation->operate($object);

        $nonExistingObject = $ioContext->getObject("non-existing-object");
        $operation = $this->getRados()->createReadOperation();
        $operation->addTask(new AssertExistsTask());
        $this->expectExceptionCode(-Errno::ENOENT->value);
        $operation->operate($nonExistingObject);
    }

    public function testAssertVersion(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");
        $version = $ioContext->getLastVersion();
        $operation->addTask(new AssertVersionTask($version));
        $operation->operate($object);

        $operation = $this->getRados()->createReadOperation();
        $operation->addTask(new AssertVersionTask($version + 1));
        $this->expectExceptionCode(-Errno::EOVERFLOW->value);
        $operation->operate($object);
    }

    public function testCompareExt(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");

        $operation->addTask(new CompareExtTask("test", 0));
        $operation->operate($object);

        $operation = $this->getRados()->createReadOperation();
        $task = new CompareExtTask("tes7", 0);
        $task->setFlags([OperationTaskFlag::FailOK]);
        $operation->addTask($task);
        $operation->operate($object);
        $this->assertEquals(3, $task->getResult());

        $operation = $this->getRados()->createReadOperation();
        $task = new CompareExtTask("tes7", 0);
        $operation->addTask($task);
        $this->expectExceptionCode(-Constants::MAX_ERRNO - 3);
        $operation->operate($object);
    }

    public function testCompareXAttribute(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");
        $object->setXAttribute("test", "1");

        $operation->addTask(new CompareXAttributeTask("test", "1"));
        $operation->addTask(new CompareXAttributeTask("test", "2", XAttributeComparisonOperator::GreaterThan));
        $operation->operate($object);

        $operation = $this->getRados()->createReadOperation();
        $operation->addTask(new CompareXAttributeTask("test", "2"));
        $this->expectExceptionCode(-Errno::ECANCELED->value);
        $operation->operate($object);
    }

    public function testCompareOMap(): void
    {
        [$ioContext, $operation, $object] = $this->init();
        $object->writeFull("test");

        $writeOp = $this->getRados()->createWriteOperation();
        $writeOp->addTask(new OMapSetTask(["test" => "1"]));
        $writeOp->operate($object);

        $operation->addTask(new OMapCompareTask("test", "1"));
        $operation->addTask(new OMapCompareTask("test", "2", OMapComparisonOperator::LessThan));
        $operation->operate($object);

        $operation = $this->getRados()->createReadOperation();
        $operation->addTask(new OMapCompareTask("test", "2"));
        $this->expectExceptionCode(-Errno::ECANCELED->value);
        $operation->operate($object);
    }
}
