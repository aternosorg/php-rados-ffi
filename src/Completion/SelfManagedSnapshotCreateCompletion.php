<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use FFI\CData;

/**
 * @extends ResultCompletion<int>
 */
class SelfManagedSnapshotCreateCompletion extends ResultCompletion
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
     * @throws RadosException
     */
    public function parseResult(): int
    {
        CompletionException::handle($this->getReturnValue());
        return $this->id->cdata;
    }
}
