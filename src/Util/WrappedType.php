<?php

namespace Aternos\Rados\Util;

use Aternos\Rados\Exception\RadosException;
use FFI;
use FFI\CData;
use WeakMap;

abstract class WrappedType
{
    /**
     * @var WeakMap<WrappedType, null> $children
     */
    protected WeakMap $children;
    private bool $isBeingReleased = false;
    private bool $released = false;
    protected CData $data;
    protected FFI $ffi;

    /**
     * @param FFI $ffi
     * @param array $array
     * @return CData
     */
    protected static function createStringArray(FFI $ffi, array $array): CData
    {
        $result = $ffi->new(FFI::arrayType($ffi->type("char*"), [count($array)]));
        foreach ($array as $i => $elem) {
            $type = FFI::arrayType($ffi->type("char"), [strlen($elem) + 1]);
            $value = $ffi->new($type, false);
            FFI::memcpy($value, $elem . "\0", strlen($elem) + 1);
            $result[$i] = $value;
        }
        return $result;
    }

    /**
     * @param CData $array
     * @return void
     */
    protected static function freeStringArray(CData $array): void
    {
        foreach ($array as $value) {
            FFI::free($value);
        }
    }

    /**
     * @param Buffer $buffer
     * @param int $length
     * @return string[]
     * @throws RadosException
     */
    protected static function parseNullTerminatedStringList(Buffer $buffer, int $length): array
    {
        $parts = explode("\0", $buffer->readString($length));
        $result = [];
        foreach ($parts as $part) {
            if ($part === "") {
                break;
            }
            $result[] = $part;
        }
        return $result;
    }

    /**
     * @param CData $data
     * @param FFI $ffi
     * @internal
     */
    public function __construct(CData $data, FFI $ffi)
    {
        $this->data = $data;
        $this->ffi = $ffi;
        $this->children = new WeakMap();
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * Get the wrapped CData object
     *
     * @return CData
     * @throws RadosException
     * @internal This method is used internally and should not be called manually
     */
    public function getCData(): CData
    {
        $this->checkValid();
        return $this->data;
    }

    /**
     * Get the wrapped CData object without checking if it is still fully valid
     * Still checks that the object has not been released
     *
     * @return CData
     * @throws RadosException
     * @internal This method is used internally and should not be called manually
     */
    protected function getCDataUnsafe(): CData
    {
        if ($this->isReleased()) {
            throw new RadosException("This object has been released and is no longer valid");
        }
        return $this->data;
    }

    /**
     * @return FFI
     * @internal This method is used internally and should not be called manually
     */
    public function getFFI(): FFI
    {
        return $this->ffi;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->isReleased();
    }

    /**
     * @return bool
     */
    public function isReleased(): bool
    {
        return $this->released;
    }

    /**
     * @return void
     * @throws RadosException
     */
    protected function checkValid(): void
    {
        if (!$this->isValid()) {
            throw new RadosException("This object has been released and is no longer valid");
        }
    }

    /**
     * Perform all cleanup operations for this object and its children
     *
     * @return $this
     */
    public final function release(): static
    {
        if (!$this->isReleased() || $this->isBeingReleased) {
            return $this;
        }
        $this->isBeingReleased = true;
        foreach ($this->children as $child => $_) {
            $child->release();
        }
        $this->releaseCData();
        $this->released = true;
        $this->isBeingReleased = false;
        return $this;
    }

    /**
     * Perform all cleanup operations for this object
     *
     * @return void
     * @internal This method is called by the release method and should not be called manually
     */
    abstract protected function releaseCData(): void;

    /**
     * Register an object
     *
     * @param WrappedType $child
     * @return void
     */
    public function registerChildObject(WrappedType $child): void
    {
        $this->children->offsetSet($child, null);
    }
}
