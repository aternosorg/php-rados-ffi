<?php

namespace Aternos\Rados\Constants;

enum LockFlag: int
{
    case MayRenew = 1<<0;
    case MustRenew = 1<<1;

    /**
     * @param static ...$flags
     * @return int
     */
    public static function combine(self ...$flags): int
    {
        $combined = 0;
        foreach ($flags as $flag) {
            $combined |= $flag->value;
        }
        return $combined;
    }
}
