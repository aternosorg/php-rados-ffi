<?php

namespace Aternos\Rados\Operation\Common;

use Aternos\Rados\Constants\OperationFlag;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\OperationTask;
use Aternos\Rados\Operation\Read\ReadOperation;
use Aternos\Rados\Operation\Write\WriteOperation;
use RuntimeException;

abstract class CommonOperationTask extends OperationTask
{
    /**
     * @inheritDoc
     */
    protected function appendToOperation(Operation $operation): void
    {
        $this->initTask($operation);
        $operation->getFFI()->{$this->getFlagsFunction($operation)}(
            $operation->getCData(),
            OperationFlag::combine($operation->getFFI(), ...$this->getFlags())
        );
    }

    /**
     * @param Operation $operation
     * @return string
     */
    protected function getFlagsFunction(Operation $operation): string
    {
        if ($operation instanceof ReadOperation) {
            return 'rados_read_op_set_flags';
        } else if ($operation instanceof WriteOperation) {
            return 'rados_write_op_set_flags';
        } else {
            throw new RuntimeException("CommonOperationTask can only be appended to a ReadOperation or WriteOperation");
        }
    }

    /**
     * @param Operation $operation
     * @return string
     */
    protected function getFunctionName(Operation $operation): string
    {
        if ($operation instanceof ReadOperation) {
            return $this->getReadFunctionName();
        } else if ($operation instanceof WriteOperation) {
            return $this->getReadFunctionName();
        } else {
            throw new RuntimeException("CommonOperationTask can only be appended to a ReadOperation or WriteOperation");
        }
    }

    /**
     * @return string
     */
    abstract protected function getReadFunctionName(): string;

    /**
     * @return string
     */
    abstract protected function getWriteFunctionName(): string;

    /**
     * @param Operation $operation
     * @return void
     */
    abstract protected function initTask(Operation $operation): void;
}
