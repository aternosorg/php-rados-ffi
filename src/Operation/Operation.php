<?php

namespace Aternos\Rados\Operation;

use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Completion\OperationCompletion;
use Aternos\Rados\Constants\OperationFlag;
use Aternos\Rados\Util\TimeSpec;
use Aternos\Rados\Util\WrappedType;

abstract class Operation extends WrappedType
{
    /**
     * @var OperationTask[]
     */
    protected array $tasks = [];

    /**
     * Add a task to the operation
     *
     * @param OperationTask $task
     * @return $this
     */
    public function addTask(OperationTask $task): static
    {
        $task->appendTo($this);
        $this->tasks[] = $task;
        return $this;
    }

    /**
     * Perform the operation synchronously
     *
     * @param RadosObject $object
     * @param TimeSpec|null $mtime
     * @param OperationFlag[] $flags
     * @return OperationTask[]
     */
    abstract public function operate(RadosObject $object, ?TimeSpec $mtime = null, array $flags = []): array;

    /**
     * Perform the operation asynchronously
     *
     * @param RadosObject $object
     * @param TimeSpec|null $mtime
     * @param OperationFlag[] $flags
     * @return OperationCompletion
     */
    abstract public function operatorAsync(RadosObject $object, ?TimeSpec $mtime = null, array $flags = []): OperationCompletion;

    /**
     * @return OperationTask[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }
}
