<?php

namespace Aternos\Rados\Constants;

use FFI;

trait EnumFlagCombinationTrait
{
    /**
     * @param FFI $ffi
     * @param EnumGetCValueInterface ...$flags
     * @return int
     */
    public static function combine(FFI $ffi,  EnumGetCValueInterface ...$flags): int
    {
        $combined = 0;
        foreach ($flags as $flag) {
            $combined |= $flag->getCValue($ffi);
        }
        return $combined;
    }
}
