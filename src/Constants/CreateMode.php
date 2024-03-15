<?php

namespace Aternos\Rados\Constants;

enum CreateMode: int
{
    case Idempotent = 0;
    case Exclusive = 1;
}
