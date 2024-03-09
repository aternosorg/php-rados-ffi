<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use FFI;

/**
 * @template T
 */
abstract class OperationCompletion extends Completion
{
    /**
     * @var T $result
     */
    protected mixed $result = null;
    protected bool $resultParsed = false;

    /**
     * @param IOContext $ioContext
     */
    public function __construct(protected IOContext $ioContext)
    {
        parent::__construct($this->ioContext->getFFI());
        $this->ioContext->registerChildObject($this);
    }

    /**
     * Cancel async operation
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function cancel(): static
    {
        CompletionException::handle($this->ffi->rados_aio_cancel($this->ioContext->getCData(), $this->getCData()));
        return $this;
    }

    /**
     * @return T
     */
    abstract public function parseResult();

    /**
     * @throws CompletionException
     * @throws RadosException
     * @return T
     */
    public function getResult()
    {
        if (!$this->isComplete()) {
            throw new CompletionException("Operation is not complete yet");
        }
        if (!$this->resultParsed) {
            $this->result = $this->parseResult();
            $this->resultParsed = true;
        }
        return $this->result;
    }

    /**
     * @return T
     * @throws RadosException
     */
    public function waitAndGetResult()
    {
        $this->waitForComplete();
        return $this->getResult();
    }

    /**
     * @inheritDoc
     * @internal This method should not be called directly, use getResult or waitAndGetResult instead
     */
    public function getReturnValue(): int
    {
        return parent::getReturnValue();
    }

    /**
     * @return void
     * @throws RadosException
     */
    protected function releaseCData(): void
    {
        parent::releaseCData();
        $this->cancel();
    }
}
