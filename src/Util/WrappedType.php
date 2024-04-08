<?php

namespace Aternos\Rados\Util;

use Aternos\Rados\Exception\RadosException;
use FFI;
use FFI\CData;
use RuntimeException;
use WeakMap;

abstract class WrappedType
{
    /**
     * @var WeakMap<WrappedType, null> $children
     */
    protected WeakMap $children;
    private bool $isBeingReleased = false;
    private bool $released = false;
    private CData $data;
    protected FFI $ffi;

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
     * @internal This method is used internally and should not be called manually
     */
    protected function getCDataUnsafe(): CData
    {
        if ($this->isReleased()) {
            throw new RuntimeException("This object has been released and is no longer valid");
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
     */
    protected function checkValid(): void
    {
        if (!$this->isValid()) {
            throw new RuntimeException("This object has been released and is no longer valid");
        }
    }

    /**
     * Perform all cleanup operations for this object and its children
     *
     * @return $this
     */
    public final function release(): static
    {
        if ($this->isReleased() || $this->isBeingReleased) {
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
