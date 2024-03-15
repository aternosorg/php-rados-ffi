<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;
use Aternos\Rados\Util\StringArray;
use FFI;

/**
 * Remove key/value pairs from an object whose keys are in the range
 * [$startKey, $endKey]
 *
 * @extends WriteOperationTask<null>
 */
class OMapRemoveKeyRangeTask extends WriteOperationTask
{
    /**
     * @param string $startKey - lower bound of the key range to remove
     * @param string $endKey - upper bound of the key range to remove
     */
    public function __construct(
        protected string $startKey,
        protected string $endKey
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
        $operation->getFFI()->rados_write_op_omap_rm_range2(
            $operation->getCData(),
            $this->startKey,
            strlen($this->startKey),
            $this->endKey,
            strlen($this->endKey)
        );
    }
}
