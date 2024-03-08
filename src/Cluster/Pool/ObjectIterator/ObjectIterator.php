<?php

namespace Aternos\Rados\Cluster\Pool\ObjectIterator;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\ObjectIteratorException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;
use Iterator;

class ObjectIterator extends WrappedType implements Iterator
{
    protected IOContext $ioContext;
    protected bool $closed = false;
    protected ?RadosObject $current = null;
    protected bool $end = false;

    /**
     * Binding for rados_nobjects_list_open
     * Start listing objects in a pool
     *
     * @param IOContext $ioContext
     * @return static
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function open(IOContext $ioContext): static
    {
        $ffi = $ioContext->getFFI();
        $result = $ffi->new('rados_list_ctx_t');
        ObjectIteratorException::handle($ffi->rados_nobjects_list_open($ioContext->getCData(), FFI::addr($result)));
        return new static($ioContext, $result, $ffi);
    }

    /**
     * @param IOContext $ioContext
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(IOContext $ioContext, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
        $this->ioContext = $ioContext;
    }

    public function __destruct()
    {
        if (!$this->closed) {
            $this->close();
        }
    }

    /**
     * Binding for rados_nobjects_list_get_pg_hash_position
     * Return hash position of iterator, rounded to the current PG
     *
     * @return int - current hash position, rounded to the current pg
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getPgHashPosition(): int
    {
        return $this->ffi->rados_nobjects_list_get_pg_hash_position($this->getCData());
    }

    /**
     * Binding for rados_nobjects_list_seek
     * Reposition object iterator to a different hash position
     *
     * @param int $position
     * @return int - actual (rounded) position we moved to
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function seekHashPosition(int $position): int
    {
        $result = $this->ffi->rados_nobjects_list_seek($this->getCData(), $position);
        $this->end = $this->getCursorPosition()->isAtEnd();
        return $result;
    }

    /**
     * Binding for rados_nobjects_list_seek_cursor
     * Reposition object iterator to a different position
     *
     * @param ObjectIteratorCursor $cursor
     * @return int - rounded position we moved to
     * @noinspection PhpUndefinedMethodInspection
     */
    public function seekCursor(ObjectIteratorCursor $cursor): int
    {
        $result = $this->ffi->rados_nobjects_list_seek_cursor($this->getCData(), $cursor->getCData());
        $this->end = $cursor->isAtEnd();
        return $result;
    }

    /**
     * Binding for rados_nobjects_list_next
     * Get a cursor to the current position of the iterator
     * The returned cursor must be released with $cursor->free().
     *
     * @return ObjectIteratorCursor
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getCursorPosition(): ObjectIteratorCursor
    {
        $result = $this->ffi->new('rados_object_list_cursor');
        ObjectIteratorException::handle($this->ffi->rados_nobjects_list_get_cursor($this->getCData(), FFI::addr($result)));
        return new ObjectIteratorCursor($this->ioContext, $result, $this->ffi);
    }

    /**
     * Binding for rados_nobjects_list_next
     * Get the next object name and locator in the pool
     *
     * @return RadosObject
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function getNextEntry(): RadosObject
    {
        $entry = $this->ffi->new('char*');
        $key = $this->ffi->new('char*');
        $namespace = $this->ffi->new('char*');
        ObjectIteratorException::handle($this->ffi->rados_nobjects_list_next($this->getCData(), FFI::addr($entry), FFI::addr($key), FFI::addr($namespace)));
        return new RadosObject(
            FFI::string($entry),
            !FFI::isNull($key) ? FFI::string($key) : null,
            !FFI::isNull($namespace) ? FFI::string($namespace) : null
        );
    }

    /**
     * Close the object listing handle.
     *
     * This should be called when the handle is no longer needed.
     * The handle should not be used after it has been closed.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     */
    public function close(): static
    {
        $this->ffi->rados_nobjects_list_close($this->getCData());
        $this->closed = true;
        return $this;
    }

    /**
     * @return RadosObject
     * @throws RadosException
     */
    public function current(): RadosObject
    {
        if ($this->current === null && !$this->end) {
            $this->next();
        }
        return $this->current;
    }

    /**
     * @return void
     * @throws RadosException
     */
    public function next(): void
    {
        try {
            $this->current = $this->getNextEntry();
        } catch (RadosException $e) {
            if (-$e->getCode() !== Errno::ENOENT->value) {
                throw $e;
            }
            $this->end = true;
            $this->current = null;
        }
    }

    /**
     * @return ObjectIteratorCursor
     * @throws RadosException
     */
    public function key(): ObjectIteratorCursor
    {
        return $this->getCursorPosition();
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return !$this->end && !$this->closed;
    }

    /**
     * @return void
     * @throws ObjectIteratorException
     */
    public function rewind(): void
    {
        $cursor = ObjectIteratorCursor::getAtBeginning($this->ioContext);
        $this->seekCursor($cursor);
    }
}
