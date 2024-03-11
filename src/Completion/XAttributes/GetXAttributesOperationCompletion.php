<?php

namespace Aternos\Rados\Completion\XAttributes;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Completion\OperationCompletion;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use FFI\CData;

/**
 * @extends OperationCompletion<string[]>
 */
class GetXAttributesOperationCompletion extends OperationCompletion
{
    /**
     * @param CData $iterator
     * @param IOContext $ioContext
     * @internal GetXAttributesOperationCompletion should not be instantiated by user code
     */
    public function __construct(protected CData $iterator, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function parseResult(): array
    {
        CompletionException::handle($this->getReturnValue());
        try {
            return $this->ioContext->getXAttributesFromIterator($this->iterator);
        } catch (RadosException $e) {
            $this->ioContext->getFFI()->rados_getxattrs_end($this->iterator);
            throw $e;
        }
    }
}
