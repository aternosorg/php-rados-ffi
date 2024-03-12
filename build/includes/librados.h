typedef long int time_t;
typedef long int suseconds_t;
struct timeval
{
  time_t tv_sec;
  suseconds_t tv_usec;
};

#include <rados/librados.h>
