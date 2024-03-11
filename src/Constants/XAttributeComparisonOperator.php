<?php

namespace Aternos\Rados\Constants;

enum XAttributeComparisonOperator: string implements EnumGetCValueInterface
{
    use EnumGetCValueTrait;

    case EQ = "LIBRADOS_CMPXATTR_OP_EQ";
    case NE = "LIBRADOS_CMPXATTR_OP_NE";
    case GT = "LIBRADOS_CMPXATTR_OP_GT";
    case GTE = "LIBRADOS_CMPXATTR_OP_GTE";
    case LT = "LIBRADOS_CMPXATTR_OP_LT";
    case LTE = "LIBRADOS_CMPXATTR_OP_LTE";
}
