<?php

namespace Aternos\Rados\Util\Buffer;

use FFI;
use RuntimeException;

class RadosAllocatedBuffer extends Buffer
{
    /**
     * @inheritDoc
     */
    public static function create(FFI $ffi, int $size): static
    {
        throw new RuntimeException("Not implemented");
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function releaseCData(): void
    {
        $this->ffi->rados_buffer_free($this->getCData());
    }
}
