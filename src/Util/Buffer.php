<?php

namespace Aternos\Rados\Util;

use Aternos\Rados\Exception\RadosException;
use FFI;
use FFI\CData;

class Buffer extends WrappedType
{
    /**
     * @param FFI $ffi
     * @param int $size
     * @return static
     */
    public static function create(FFI $ffi, int $size): static
    {
        $buffer = $ffi->new(FFI::arrayType($ffi->type("char"), [$size]));
        return new static($size, $buffer, $ffi);
    }

    /**
     * @param int $size
     * @param CData $data
     * @param FFI $ffi
     * @internal Use Buffer::create to create a new buffer
     */
    public function __construct(protected int $size, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Convert this buffer to a string
     *
     * @return string
     * @throws RadosException
     */
    public function toString(): string
    {
        return FFI::string($this->getCData(), $this->getSize());
    }

    /**
     * Read a string from the buffer
     * If length is null, the buffer is read until the first null byte
     *
     * @param int|null $length
     * @return string
     * @throws RadosException
     */
    public function readString(?int $length = null): string
    {
        return FFI::string($this->getCData(), $length);
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        //No manual release needed
    }
}
