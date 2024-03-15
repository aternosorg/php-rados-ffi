<?php

namespace Aternos\Rados\Cluster\Pool\Object\OMap;

use Aternos\Rados\Cluster\Pool\Object\AttributePair;
use Aternos\Rados\Exception\OMapIteratorException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Util\WrappedType;
use Countable;
use FFI;
use FFI\CData;
use Iterator;

/**
 * Class OMapIterator
 * Iterate over omap key/value pairs
 *
 * @note This iterator does not implement the rewind method as it is not possible to rewind an omap iterator
 * It can therefore only be used once
 */
class OMapIterator extends WrappedType implements Iterator, Countable
{
    protected ?AttributePair $current = null;
    protected bool $end = false;

    /**
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
    }

    /**
     * Binding for rados_omap_get_keys2
     * Get the next omap key/value pair on the object.
     *
     * @return AttributePair|null
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function getNextEntry(): ?AttributePair
    {
        $key = $this->ffi->new('char*');
        $value = $this->ffi->new('char*');
        $keyLength = $this->ffi->new('size_t');
        $valueLength = $this->ffi->new('size_t');
        OMapIteratorException::handle($this->ffi->rados_omap_get_next2(
            $this->getCData(),
            FFI::addr($key),
            FFI::addr($value),
            FFI::addr($keyLength),
            FFI::addr($valueLength)
        ));
        if (FFI::isNull($key) || FFI::isNull($value)) {
            return null;
        }

        return new AttributePair(
            FFI::string($key, $keyLength->cdata),
            FFI::string($value, $valueLength->cdata)
        );
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function current(): string
    {
        if ($this->current === null && !$this->end) {
            $this->next();
        }
        return $this->current->getValue();
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function next(): void
    {
        $this->current = $this->getNextEntry();
        if ($this->current === null) {
            $this->end = true;
        }
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function key(): string
    {
        if ($this->current === null && !$this->end) {
            $this->next();
        }
        return $this->current->getKey();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->isValid() && !$this->end;
    }

    /**
     * @note This method is not implemented as it is not possible to rewind an omap iterator
     * @inheritDoc
     */
    public function rewind(): void
    {
        // Do nothing
    }

    /**
     * Binding for rados_omap_iter_size
     * Return number of elements in the iterator
     *
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function count(): int
    {
        return $this->ffi->rados_omap_iter_size($this->getCData());
    }

    /**
     * Binding for rados_omap_get_end
     * Close the omap iterator.
     *
     * @inheritDoc
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function releaseCData(): void
    {
        $this->ffi->rados_omap_get_end($this->getCData());
    }
}
