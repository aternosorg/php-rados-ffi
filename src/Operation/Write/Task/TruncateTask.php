<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Truncate an object
 *
 * @extends WriteOperationTask<null>
 */
class TruncateTask extends WriteOperationTask
{
    /**
     * @param int $size - new size of the object
     */
    public function __construct(
        protected int $size
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
        $operation->getFFI()->rados_write_op_truncate(
            $operation->getCData(),
            $this->size
        );
    }
}
