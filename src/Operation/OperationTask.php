<?php

namespace Aternos\Rados\Operation;

use Aternos\Rados\Constants\OperationTaskFlag;
use RuntimeException;

/**
 * @template T
 */
abstract class OperationTask
{
    /**
     * @var OperationTaskFlag[]
     */
    protected array $flags = [];
    protected ?Operation $operation = null;

    /**
     * @var T $result
     */
    protected mixed $parsedResult = null;
    protected bool $resultParsed = false;

    /**
     * Parse the result of the operation
     *
     * @return T
     */
    abstract protected function parseResult(): mixed;

    /**
     * Get the result of the operation
     *
     * @return T
     */
    public function getResult(): mixed
    {
        if (!$this->resultParsed) {
            $this->parsedResult = $this->parseResult();
            $this->resultParsed = true;
        }
        return $this->parsedResult;
    }

    /**
     * Append this task to an operation
     * A task can only be appended to one operation
     *
     * @param Operation $operation
     * @return $this
     * @internal Use Operation::addTask() instead
     */
    public final function appendTo(Operation $operation): static
    {
        if ($this->operation !== null) {
            throw new RuntimeException("OperationTask can only be appended to one operation");
        }
        $this->appendToOperation($operation);
        $this->operation = $operation;
        return $this;
    }

    /**
     * @param Operation $operation
     * @return void
     */
    abstract protected function appendToOperation(Operation $operation): void;

    /**
     * Set flags for this task
     *
     * @param OperationTaskFlag[] $flags
     * @return $this
     */
    public function setFlags(array $flags): OperationTask
    {
        if ($this->operation !== null) {
            throw new RuntimeException("Flags can only be set before appending the task to an operation");
        }
        $this->flags = $flags;
        return $this;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }
}
