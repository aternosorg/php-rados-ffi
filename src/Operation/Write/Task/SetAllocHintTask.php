<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Constants\AllocHintFlag;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Set allocation hint for an object
 *
 * @extends WriteOperationTask<null>
 */
class SetAllocHintTask extends WriteOperationTask
{
    /**
     * @param int $expectedSize - expected size of the object
     * @param int $expectedWriteSize - expected write size of the object
     * @param AllocHintFlag[]|null $allocFlags
     */
    public function __construct(
        protected int $expectedSize,
        protected int $expectedWriteSize,
        protected ?array $allocFlags = null
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
        $flagValue = null;
        if ($this->allocFlags !== null) {
            $flagValue = AllocHintFlag::combine($this->getIOContext()->getFFI(), ...$this->allocFlags);
        }

        if ($flagValue === null) {
            $operation->getFFI()->rados_write_op_set_alloc_hint(
                $operation->getCData(),
                $this->expectedSize,
                $this->expectedWriteSize
            );
        } else {
            $operation->getFFI()->rados_write_op_set_alloc_hint2(
                $operation->getCData(),
                $this->expectedSize,
                $this->expectedWriteSize,
                $flagValue
            );
        }
    }
}
