<?php

namespace Aternos\Rados\Cluster\Pool\ObjectIterator;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\RadosException;
use FFI;
use InvalidArgumentException;

/**
 * Range between a start cursor (inclusive) and an end cursor (exclusive)
 */
class ObjectRange
{
    /**
     * @param IOContext $ioContext
     * @param ObjectCursor $start
     * @param ObjectCursor $end
     * @internal Use IOContext::getObjectRange() instead
     */
    public function __construct(protected IOContext $ioContext, protected ObjectCursor $start, protected ObjectCursor $end)
    {
    }

    /**
     * @return ObjectCursor
     */
    public function getStart(): ObjectCursor
    {
        return $this->start;
    }

    /**
     * @return ObjectCursor
     */
    public function getEnd(): ObjectCursor
    {
        return $this->end;
    }

    /**
     * Binding for rados_object_list_slice
     * Obtain cursors delineating a subset of a range.  Use this
     * when you want to split up the work of iterating over the
     * global namespace.  Expected use case is when you are iterating
     * in parallel, with `m` workers, and each worker taking an id `n`.
     *
     * @param int $chunkIndex - the index of the chunk to get
     * @param int $chunkCount - many chunks to divide this range into
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function slice(int $chunkIndex, int $chunkCount): static
    {
        if ($chunkCount < 1) {
            throw new InvalidArgumentException("Chunk count must be at least 1");
        }

        if ($chunkIndex < 0 || $chunkIndex >= $chunkCount) {
            throw new InvalidArgumentException("Chunk index must be between 0 and chunk count - 1");
        }

        $ffi = $this->ioContext->getFFI();
        $newStart = $ffi->new('rados_object_list_cursor');
        $newEnd = $ffi->new('rados_object_list_cursor');
        $ffi->rados_object_list_slice(
            $this->ioContext->getCData(),
            $this->start->getCData(),
            $this->end->getCData(),
            $chunkIndex,
            $chunkCount,
            FFI::addr($newStart),
            FFI::addr($newEnd)
        );

        return new static(
            $this->ioContext,
            new ObjectCursor($this->ioContext, $newStart, $ffi),
            new ObjectCursor($this->ioContext, $newEnd, $ffi)
        );
    }
}
