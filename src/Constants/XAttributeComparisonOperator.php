<?php

namespace Aternos\Rados\Constants;

enum XAttributeComparisonOperator: string implements EnumGetCValueInterface
{
    use EnumGetCValueTrait;

    case Equal = "LIBRADOS_CMPXATTR_OP_EQ";
    case NotEqual = "LIBRADOS_CMPXATTR_OP_NE";
    case GreaterThan = "LIBRADOS_CMPXATTR_OP_GT";
    case GreaterThanEqual = "LIBRADOS_CMPXATTR_OP_GTE";
    case LessThan = "LIBRADOS_CMPXATTR_OP_LT";
    case LessThanEqual = "LIBRADOS_CMPXATTR_OP_LTE";
}
