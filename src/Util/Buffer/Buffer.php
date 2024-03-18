<?php

namespace Aternos\Rados\Util\Buffer;

use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;
use RuntimeException;

class Buffer extends WrappedType
{
    const GROWTH_FACTOR = 1.6;

    /**
     * @param int $size
     * @return int
     */
    public static function grow(int $size): int
    {
        return (int) max($size * static::GROWTH_FACTOR, 1);
    }

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
     * @param string $data
     * @return $this
     */
    public function write(string $data): static
    {
        if (strlen($data) > $this->size) {
            throw new RuntimeException("Buffer overflow");
        }
        FFI::memcpy($this->getCData(), $data, strlen($data));
        return $this;
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
     */
    public function readString(?int $length = null): string
    {
        return FFI::string($this->getCData(), $length);
    }

    /**
     * @return string[]
     */
    public function readNullTerminatedStringList(?int $length = null, bool $stopOnEmptyString = true): array
    {
        $parts = explode("\0", $this->readString($length ?? $this->getSize()));
        array_pop($parts);

        $result = [];
        foreach ($parts as $part) {
            if ($part === "" && $stopOnEmptyString) {
                break;
            }
            $result[] = $part;
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        //No manual release needed
    }
}
