<?php

namespace Aternos\Rados\Constants;

use FFI;

interface EnumGetCValueInterface
{
    public function getCValue(FFI $ffi): int;
}
