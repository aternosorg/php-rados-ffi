<?php

namespace Aternos\Rados\Constants;

enum OMapComparisonOperator: string implements EnumGetCValueInterface
{
    use EnumGetCValueTrait;

    case Equal = "LIBRADOS_CMPXATTR_OP_EQ";
    case GreaterThan = "LIBRADOS_CMPXATTR_OP_GT";
    case LessThan = "LIBRADOS_CMPXATTR_OP_LT";
}
