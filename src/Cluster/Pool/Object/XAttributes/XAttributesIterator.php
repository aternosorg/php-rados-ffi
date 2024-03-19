<?php

namespace Aternos\Rados\Cluster\Pool\Object\XAttributes;

use Aternos\Rados\Cluster\Pool\Object\AttributePair;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\XAttributeIteratorException;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;
use Iterator;

/**
 * Class XAttributes
 *
 * @note This iterator does not implement the rewind method as it is not possible to rewind an xattribute iterator
 * It can therefore only be used once
 */
class XAttributesIterator extends WrappedType implements Iterator
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
     * Binding for rados_getxattrs_next
     * Get the next xattr on the object
     *
     * @return AttributePair|null
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function getNextEntry(): ?AttributePair
    {
        $key = $this->ffi->new('char*');
        $value = $this->ffi->new('char*');
        $size = $this->ffi->new('size_t');

        XAttributeIteratorException::handle($this->ffi->rados_getxattrs_next(
            $this->getCData(),
            FFI::addr($key),
            FFI::addr($value), FFI::addr($size)
        ));

        if (FFI::isNull($key) || FFI::isNull($value)) {
            return null;
        }

        return new AttributePair(
            FFI::string($key),
            FFI::string($value, $size->cdata)
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
     * @note This method is not implemented as it is not possible to rewind an xattribute iterator
     * @inheritDoc
     */
    public function rewind(): void
    {
        // Do nothing
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function releaseCData(): void
    {
        $this->ffi->rados_getxattrs_end($this->getCData());
    }
}
