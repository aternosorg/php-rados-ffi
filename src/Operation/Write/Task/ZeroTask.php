<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Zero part of an object
 *
 * @extends WriteOperationTask<null>
 */
class ZeroTask extends WriteOperationTask
{
    /**
     * @param int $offset
     * @param int $length
     */
    public function __construct(
        protected int $offset,
        protected int $length
    )
    {
    }

    /**
     * @inheritDoc
     */
    protected function parseResult(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $operation->getFFI()->rados_write_op_zero(
            $operation->getCData(),
            $this->offset,
            $this->length
        );
    }
}
