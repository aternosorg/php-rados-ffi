<?php

namespace Aternos\Rados\Completion\XAttributes;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\XAttributes\XAttributesIterator;
use Aternos\Rados\Completion\ResultCompletion;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use FFI\CData;

/**
 * @extends ResultCompletion<XAttributesIterator>
 */
class GetXAttributesCompletion extends ResultCompletion
{
    /**
     * @param CData $iterator
     * @param IOContext $ioContext
     * @internal Completions are returned from async operations and should not be created manually
     */
    public function __construct(protected CData $iterator, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult(): XAttributesIterator
    {
        CompletionException::handle($this->getReturnValue());
        return new XAttributesIterator($this->ioContext, $this->iterator, $this->ioContext->getFFI());
    }
}
