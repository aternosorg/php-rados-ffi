<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;
use FFI;
use FFI\CData;

/**
 * Execute an OSD class method on an object
 * See rados_exec() for general description.
 *
 * @extends WriteOperationTask<int>
 */
class ExecuteTask extends WriteOperationTask
{
    protected ?CData $result = null;

    /**
     * @param string $class - name of the class
     * @param string $method - name of the method
     * @param string $input - input buffer
     */
    public function __construct(
        protected string $class,
        protected string $method,
        protected string $input,
    )
    {
    }

    /**
     * @inheritDoc
     */
    protected function parseResult(): int
    {
        return $this->result?->cdata ?? 0;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $operation->getFFI()->rados_write_op_exec(
            $operation->getCData(),
            $this->class,
            $this->method,
            $this->input,
            strlen($this->input),
            FFI::addr($this->result)
        );
    }
}
