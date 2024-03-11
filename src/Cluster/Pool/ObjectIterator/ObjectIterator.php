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
    protected ?ObjectEntry $current = null;
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
     * @internal ObjectIterator objects can be obtained from the IOContext object or created using ObjectIterator::open() and should not be created directly
     */
    public function __construct(IOContext $ioContext, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
        $this->ioContext = $ioContext;
        $this->ioContext->registerChildObject($this);
    }

    /**
     * Binding for rados_nobjects_list_get_pg_hash_position
     * Return hash position of iterator, rounded to the current PG
     *
     * @return int - current hash position, rounded to the current pg
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
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
     * @param ObjectCursor $cursor
     * @return int - rounded position we moved to
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function seekCursor(ObjectCursor $cursor): int
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
     * @return ObjectCursor
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getCursorPosition(): ObjectCursor
    {
        $result = $this->ffi->new('rados_object_list_cursor');
        ObjectIteratorException::handle($this->ffi->rados_nobjects_list_get_cursor($this->getCData(), FFI::addr($result)));
        return new ObjectCursor($this->ioContext, $result, $this->ffi);
    }

    /**
     * Binding for rados_nobjects_list_next
     * Get the next object name and locator in the pool
     *
     * @return ObjectEntry
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function getNextEntry(): ObjectEntry
    {
        $entry = $this->ffi->new('char*');
        $key = $this->ffi->new('char*');
        $namespace = $this->ffi->new('char*');
        ObjectIteratorException::handle($this->ffi->rados_nobjects_list_next($this->getCData(), FFI::addr($entry), FFI::addr($key), FFI::addr($namespace)));
        if (FFI::isNull($entry)) {
            throw new ObjectIteratorException("Failed to get next object entry");
        }

        return new ObjectEntry(
            $this->ioContext,
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
     * @throws RadosException
     * @internal This method is called by the release method and should not be called manually
     */
    protected function close(): static
    {
        $this->ffi->rados_nobjects_list_close($this->getCDataUnsafe());
        return $this;
    }

    /**
     * @return ObjectEntry
     * @throws RadosException
     */
    public function current(): ObjectEntry
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
     * @return ObjectCursor
     * @throws RadosException
     */
    public function key(): ObjectCursor
    {
        return $this->getCursorPosition();
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->isValid() && !$this->end;
    }

    /**
     * @return void
     * @throws ObjectIteratorException|RadosException
     */
    public function rewind(): void
    {
        $cursor = ObjectCursor::getAtBeginning($this->ioContext);
        $this->seekCursor($cursor);
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return parent::isValid() && $this->ioContext->isValid();
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->close();
    }
}
