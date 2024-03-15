<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Remove all key/value pairs from an object
 *
 * @extends WriteOperationTask<null>
 */
class OMapClearKeyTask extends WriteOperationTask
{
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
        $operation->getFFI()->rados_write_op_omap_clear(
            $operation->getCData()
        );
    }
}
