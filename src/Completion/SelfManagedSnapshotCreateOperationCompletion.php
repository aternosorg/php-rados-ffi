<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\CompletionException;
use FFI\CData;

/**
 * @extends OperationCompletion<int>
 */
class SelfManagedSnapshotCreateOperationCompletion extends OperationCompletion
{
    /**
     * @param CData $id
     * @param IOContext $ioContext
     */
    public function __construct(protected CData $id, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     */
    public function parseResult(): int
    {
        CompletionException::handle($this->getReturnValue());
        return $this->id->cdata;
    }
}
