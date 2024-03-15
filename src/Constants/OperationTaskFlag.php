<?php

namespace Aternos\Rados\Constants;

enum OperationTaskFlag: string implements EnumGetCValueInterface
{
    use EnumGetCValueTrait;
    use EnumFlagCombinationTrait;

    case Excl = "LIBRADOS_OP_FLAG_EXCL";
    case FailOK = "LIBRADOS_OP_FLAG_FAILOK";
    case FAdviseRandom = "LIBRADOS_OP_FLAG_FADVISE_RANDOM";
    case FAdviseSequential = "LIBRADOS_OP_FLAG_FADVISE_SEQUENTIAL";
    case FAdviseWillNeed = "LIBRADOS_OP_FLAG_FADVISE_WILLNEED";
    case FAdviseDontNeed = "LIBRADOS_OP_FLAG_FADVISE_DONTNEED";
    case FAdviseNoCache = "LIBRADOS_OP_FLAG_FADVISE_NOCACHE";
    case FAdviseFUA = "LIBRADOS_OP_FLAG_FADVISE_FUA";
}
