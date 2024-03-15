<?php

namespace Aternos\Rados\Constants;

enum AllocHintFlag: string implements EnumGetCValueInterface
{
    use EnumGetCValueTrait;
    use EnumFlagCombinationTrait;

    case SequentialWrite = "LIBRADOS_ALLOC_HINT_FLAG_SEQUENTIAL_WRITE";
    case RandomWrite = "LIBRADOS_ALLOC_HINT_FLAG_RANDOM_WRITE";
    case SequentialRead = "LIBRADOS_ALLOC_HINT_FLAG_SEQUENTIAL_READ";
    case RandomRead = "LIBRADOS_ALLOC_HINT_FLAG_RANDOM_READ";
    case AppendOnly = "LIBRADOS_ALLOC_HINT_FLAG_APPEND_ONLY";
    case Immutable = "LIBRADOS_ALLOC_HINT_FLAG_IMMUTABLE";
    case ShortLived = "LIBRADOS_ALLOC_HINT_FLAG_SHORTLIVED";
    case LongLived = "LIBRADOS_ALLOC_HINT_FLAG_LONGLIVED";
    case Compressible = "LIBRADOS_ALLOC_HINT_FLAG_COMPRESSIBLE";
    case Incompressible = "LIBRADOS_ALLOC_HINT_FLAG_INCOMPRESSIBLE";
}
