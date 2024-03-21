<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Pool\IOContext;
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
}
