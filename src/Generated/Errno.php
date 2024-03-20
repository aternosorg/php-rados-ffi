<?php

namespace Aternos\Rados\Generated;

enum Errno: int
{
    case EMULTIHOP = 72;
    case EUNATCH = 49;
    case EAFNOSUPPORT = 97;
    case ELIBMAX = 82;
    case EREMCHG = 78;
    case EACCES = 13;
    case EDESTADDRREQ = 89;
    case EILSEQ = 84;
    case ESPIPE = 29;
    case EMLINK = 31;
    case EOWNERDEAD = 130;
    case ENOTTY = 25;
    case EBADE = 52;
    case EBADF = 9;
    case EBADR = 53;
    case EADV = 68;
    case ERANGE = 34;
    case ECANCELED = 125;
    case ETXTBSY = 26;
    case ENOMEM = 12;
    case EINPROGRESS = 115;
    case ENOTEMPTY = 39;
    case ENOTBLK = 15;
    case EPROTOTYPE = 91;
    case ERESTART = 85;
    case EISNAM = 120;
    case ENOMSG = 42;
    case ENOTDIR = 20;
    case EALREADY = 114;
    case ETIMEDOUT = 110;
    case ENODATA = 61;
    case EINTR = 4;
    case ENOLINK = 67;
    case EPERM = 1;
    case ESTALE = 116;
    case ENOTSOCK = 88;
    case ENOSR = 63;
    case ECHILD = 10;
    case EBADMSG = 74;
    case ELNRNG = 48;
    case ENOTUNIQ = 76;
    case ENOSYS = 38;
    case EDEADLK = 35;
    case EPIPE = 32;
    case EBFONT = 59;
    case ERFKILL = 132;
    case EREMOTE = 66;
    case ETOOMANYREFS = 109;
    case EPFNOSUPPORT = 96;
    case ESRMNT = 69;
    case ENONET = 64;
    case ENOTNAM = 118;
    case ELIBEXEC = 83;
    case ENOCSI = 50;
    case EADDRINUSE = 98;
    case ENETRESET = 102;
    case EISDIR = 21;
    case EIDRM = 43;
    case ECONNABORTED = 103;
    case EHOSTUNREACH = 113;
    case EBADFD = 77;
    case EL3HLT = 46;
    case EL2HLT = 51;
    case ENOKEY = 126;
    case EINVAL = 22;
    case EADDRNOTAVAIL = 99;
    case ESHUTDOWN = 108;
    case EKEYREJECTED = 129;
    case ELIBSCN = 81;
    case ENAVAIL = 119;
    case ENOSTR = 60;
    case EMFILE = 24;
    case EOVERFLOW = 75;
    case EUCLEAN = 117;
    case ENOMEDIUM = 123;
    case EBUSY = 16;
    case EPROTO = 71;
    case ENODEV = 19;
    case EKEYEXPIRED = 127;
    case EROFS = 30;
    case ELIBACC = 79;
    case EHWPOISON = 133;
    case E2BIG = 7;
    case ECONNRESET = 104;
    case ENXIO = 6;
    case EBADRQC = 56;
    case EL3RST = 47;
    case ENAMETOOLONG = 36;
    case ESOCKTNOSUPPORT = 94;
    case EDOTDOT = 73;
    case ETIME = 62;
    case EPROTONOSUPPORT = 93;
    case ENOTRECOVERABLE = 131;
    case EIO = 5;
    case ENETUNREACH = 101;
    case EXDEV = 18;
    case EDQUOT = 122;
    case EREMOTEIO = 121;
    case ENOSPC = 28;
    case ENOEXEC = 8;
    case EMSGSIZE = 90;
    case EBADSLT = 57;
    case EDOM = 33;
    case EFBIG = 27;
    case ESRCH = 3;
    case ECHRNG = 44;
    case EHOSTDOWN = 112;
    case ENOLCK = 37;
    case ENFILE = 23;
    case ENOTCONN = 107;
    case ENOANO = 55;
    case EISCONN = 106;
    case EUSERS = 87;
    case ENETDOWN = 100;
    case ENOPROTOOPT = 92;
    case ECOMM = 70;
    case ELOOP = 40;
    case ENOBUFS = 105;
    case EFAULT = 14;
    case ELIBBAD = 80;
    case ESTRPIPE = 86;
    case ECONNREFUSED = 111;
    case EAGAIN = 11;
    case EEXIST = 17;
    case EL2NSYNC = 45;
    case ENOENT = 2;
    case ENOPKG = 65;
    case EXFULL = 54;
    case EKEYREVOKED = 128;
    case EOPNOTSUPP = 95;
    case EMEDIUMTYPE = 124;

    /**
     * @param int $errno
     * @return string|null
     */
    static function getErrorName(int $errno): ?string
    {
        foreach (self::cases() as $error) {
            if ($error->value === $errno) {
                return $error->name;
            }
        }
        return null;
    }
}
