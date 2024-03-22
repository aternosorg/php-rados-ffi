<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Cluster\Pool\Object\ObjectStat;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Read\ReadOperationTask;
use Aternos\Rados\Util\TimeSpec;
use FFI;
use FFI\CData;
use RuntimeException;

/**
 * @extends ReadOperationTask<ObjectStat>
 */
class StatTask extends ReadOperationTask
{
    protected ?CData $size = null;
    protected ?CData $mTime = null;
    protected ?CData $result = null;

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): ObjectStat
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        RadosObjectException::handle($this->result->cdata);
        return new ObjectStat(
            $this->size->cdata,
            TimeSpec::fromCData($this->mTime)
        );
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $this->size = $operation->getFFI()->new('uint64_t');
        $this->mTime = $operation->getFFI()->new('struct timespec');

        $operation->getFFI()->rados_read_op_stat2(
            $operation->getCData(),
            FFI::addr($this->size),
            FFI::addr($this->mTime),
            FFI::addr($this->result)
        );
    }
}
