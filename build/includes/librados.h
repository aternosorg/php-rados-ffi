typedef long int time_t;
typedef long int suseconds_t;
struct timeval
{
  time_t tv_sec;
  suseconds_t tv_usec;
};

struct timespec
{
  time_t tv_sec;
  long int tv_nsec;
};

#include <rados/librados.h>
