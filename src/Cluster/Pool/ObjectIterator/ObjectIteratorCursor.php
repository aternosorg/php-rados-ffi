<?php

namespace Aternos\Rados\Cluster\Pool\ObjectIterator;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\ObjectIteratorException;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;

class ObjectIteratorCursor extends WrappedType
{
    protected bool $closed = false;

    /**
     * Binding for rados_object_list_begin
     * Get cursor handle pointing to the *beginning* of a pool.
     *
     * This is an opaque handle pointing to the start of a pool.
     *
     * @param IOContext $ioContext
     * @return ObjectIteratorCursor
     * @throws ObjectIteratorException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function getAtBeginning(IOContext $ioContext): ObjectIteratorCursor
    {
        $ffi = $ioContext->getFFI();
        $result = $ffi->rados_object_list_begin($ioContext->getCData());
        if ($result === null) {
            throw new ObjectIteratorException("Failed to get object list cursor");
        }
        return new ObjectIteratorCursor($ioContext, $result, $ffi);
    }

    /**
     * Binding for rados_object_list_end
     * Get cursor handle pointing to the *end* of a pool.
     *
     * This is an opaque handle pointing to the end of a pool.
     *
     * @param IOContext $ioContext
     * @return ObjectIteratorCursor
     * @throws ObjectIteratorException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function getAtEnd(IOContext $ioContext): ObjectIteratorCursor
    {
        $ffi = $ioContext->getFFI();
        $result = $ffi->rados_object_list_end($ioContext->getCData());
        if ($result === null) {
            throw new ObjectIteratorException("Failed to get object list cursor");
        }
        return new ObjectIteratorCursor($ioContext, $result, $ffi);
    }

    /**
     * @param IOContext $ioContext
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(protected IOContext $ioContext, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
    }

    public function __destruct()
    {
        if (!$this->closed) {
            $this->free();
        }
    }

    /**
     * Binding for rados_object_list_cursor_free
     * Release a cursor. The handle may not be used after this point.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     */
    public function free(): static
    {
        $this->ffi->rados_object_list_cursor_free($this->ioContext->getCData(), $this->getCData());
        $this->closed = true;
        return $this;
    }

    /**
     * Binding for rados_object_list_is_end
     * Check if a cursor has reached the end of a pool
     *
     * @return bool
     * @noinspection PhpUndefinedMethodInspection
     */
    public function isAtEnd(): bool
    {
        return (bool)$this->ffi->rados_object_list_is_end($this->ioContext->getCData(), $this->getCData());
    }

    /**
     * Binding for rados_object_list_cursor_cmp
     * Compare two cursor positions
     *
     * Compare two cursors, and indicate whether the first cursor precedes,
     * matches, or follows the second.
     *
     * @param ObjectIteratorCursor $other
     * @return int - -1, 0, or 1 for lhs < rhs, lhs == rhs, or lhs > rhs
     * @noinspection PhpUndefinedMethodInspection
     */
    public function compare(ObjectIteratorCursor $other): int
    {
        return $this->ffi->rados_object_list_cursor_cmp($this->ioContext->getCData(), $this->getCData(), $other->getCData());
    }
}
