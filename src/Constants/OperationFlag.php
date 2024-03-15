<?php

namespace Aternos\Rados\Constants;

enum OperationFlag: string implements EnumGetCValueInterface
{
    use EnumGetCValueTrait;
    use EnumFlagCombinationTrait;

    case NoFlag = "LIBRADOS_OPERATION_NOFLAG";
    case BalanceReads = "LIBRADOS_OPERATION_BALANCE_READS";
    case LocalizeReads = "LIBRADOS_OPERATION_LOCALIZE_READS";
    case OrderReadsWrites = "LIBRADOS_OPERATION_ORDER_READS_WRITES";
    case IgnoreCache = "LIBRADOS_OPERATION_IGNORE_CACHE";
    case SkipRWLocks = "LIBRADOS_OPERATION_SKIPRWLOCKS";
    case IgnoreOverlay = "LIBRADOS_OPERATION_IGNORE_OVERLAY";
    case FullTry = "LIBRADOS_OPERATION_FULL_TRY";
    case FullForce = "LIBRADOS_OPERATION_FULL_FORCE";
    case IgnoreRedirect = "LIBRADOS_OPERATION_IGNORE_REDIRECT";
    case OrderSnap = "LIBRADOS_OPERATION_ORDERSNAP";
    case ReturnVec = "LIBRADOS_OPERATION_RETURNVEC";
}
