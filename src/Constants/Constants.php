<?php

namespace Aternos\Rados\Constants;

class Constants
{
    const MAX_ERRNO = 4095;
    const LOCK_FLAG_RENEW = 1<<0;
    const SNAP_HEAD = -2;
    const SNAP_DIR = -1;
    const LOCK_FLAG_MUST_RENEW = 1<<1;
    const CREATE_IDEMPOTENT = 0;
    const CREATE_EXCLUSIVE = 1;
    const LOCK_FLAG_MAY_RENEW = self::LOCK_FLAG_RENEW;
    const ALL_NSPACES = "\001";
}
