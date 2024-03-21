<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Operation\OperationTask;

/**
 * @extends ResultCompletion<OperationTask[]>
 */
class OperationCompletion extends ResultCompletion
{
    /**
     * @param OperationTask[] $tasks
     * @param IOContext $ioContext
     */
    public function __construct(protected array $tasks, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult()
    {
        CompletionException::handle($this->getReturnValue(), true);
        return $this->tasks;
    }
}
