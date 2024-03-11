<?php

namespace Aternos\Rados\Constants;

use FFI;

trait EnumGetCValueTrait
{
    /**
     * @param FFI $ffi
     * @return int
     */
    public function getCValue(FFI $ffi): int
    {
        return $ffi->{$this->value};
    }
}
