<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;
use Aternos\Rados\Util\Buffer\Buffer;
use FFI;
use FFI\CData;
use RuntimeException;

/**
 * Execute an OSD class method on an object
 * See rados_exec() for general description.
 *
 * @extends WriteOperationTask<string>
 */
class ExecuteTask extends WriteOperationTask
{
    protected ?CData $result = null;
    protected ?CData $output = null;
    protected ?CData $outputLength = null;

    /**
     * @param string $class - name of the class
     * @param string $method - name of the method
     * @param string $input - input buffer
     * @param Buffer|null $outputBuffer - Optional: user-provided buffer to read into
     * If provided, but too small for the result, the operation will fail
     */
    public function __construct(
        protected string $class,
        protected string $method,
        protected string $input,
        protected ?Buffer $outputBuffer = null
    )
    {
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function parseResult(): string
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        if ($this->outputBuffer !== null) {
            return $this->outputBuffer->readString($this->outputLength->cdata);
        }

        $output = FFI::string($this->output, $this->outputLength->cdata);
        $this->operation->getFFI()->rados_buffer_free($this->output);
        return $output;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $this->output = $operation->getFFI()->new('char*');
        $this->outputLength = $operation->getFFI()->new('size_t');

        if ($this->outputBuffer !== null) {
            $operation->getFFI()->rados_read_op_exec_user_buf(
                $operation->getCData(),
                $this->class,
                $this->method,
                $this->input,
                strlen($this->input),
                $this->outputBuffer->getCData(),
                $this->outputBuffer->getSize(),
                FFI::addr($this->outputLength),
                FFI::addr($this->result)
            );
            return;
        }

        $operation->getFFI()->rados_read_op_exec(
            $operation->getCData(),
            $this->class,
            $this->method,
            $this->input,
            strlen($this->input),
            FFI::addr($this->output),
            FFI::addr($this->outputLength),
            FFI::addr($this->result)
        );
    }
}
