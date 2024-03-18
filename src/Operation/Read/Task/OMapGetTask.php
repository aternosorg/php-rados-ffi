<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Cluster\Pool\Object\OMap\OMapIterator;
use Aternos\Rados\Cluster\Pool\Object\OMap\OMapReadResult;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Read\ReadOperationTask;
use FFI;
use FFI\CData;
use RuntimeException;

/**
 * Start iterating over key/value pairs on an object.
 *
 * @extends ReadOperationTask<OMapReadResult>
 */
class OMapGetTask extends ReadOperationTask
{
    protected ?CData $result = null;
    protected ?CData $iterator = null;
    protected ?CData $hasMore = null;

    /**
     * @param int|null $maxEntries - list no more than $maxEntries key/value pairs
     * @param string|null $startAfter - list keys starting after start_after
     * @param string|null $filterPrefix - list only keys beginning with filter_prefix
     */
    public function __construct(
        protected ?int $maxEntries,
        protected ?string $startAfter = null,
        protected ?string $filterPrefix = null
    )
    {
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): OMapReadResult
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        RadosObjectException::handle($this->result->cdata);

        return new OMapReadResult(
            new OMapIterator($this->iterator, $this->operation->getFFI()),
            $this->hasMore->cdata
        );
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $this->iterator = $operation->getFFI()->new('rados_omap_iter_t');
        $this->hasMore = $operation->getFFI()->new('uint8_t');

        $operation->getFFI()->rados_read_op_omap_get_vals2(
            $operation->getCData(),
            $this->startAfter,
            $this->filterPrefix,
            $this->maxEntries,
            FFI::addr($this->iterator),
            FFI::addr($this->hasMore),
            FFI::addr($this->result)
        );
    }
}
