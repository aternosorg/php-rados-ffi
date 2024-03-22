/*
This file is generated, do not modify it directly!
To generate this file, run `./bin/generate-headers`, or `./vendor/bin/generate-headers`
if this was installed as a dependency using composer.

GENERATED ON 2024-03-22 13:14:58

This file is generated from librados.h and rados_types.h,
available in the librados-dev package installed when this was generated.
The source code for these files can be found at https://github.com/ceph/ceph

    Ceph - scalable distributed file system
    Copyright (C) 2004-2012 Sage Weil <sage@newdream.net>

    This is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License version 2.1, as published by the Free Software
    Foundation.  See file LICENSE.
*/


#define FFI_SCOPE "PHP_RADOS_FFI"
#define FFI_LIB "librados.so"

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
struct obj_watch_t {
  char addr[256];
  int64_t watcher_id;
  uint64_t cookie;
  uint32_t timeout_seconds;
};
struct notify_ack_t {
  uint64_t notifier_id;
  uint64_t cookie;
  char *payload;
  uint64_t payload_len;
};
struct notify_timeout_t {
  uint64_t notifier_id;
  uint64_t cookie;
};
enum {
  LIBRADOS_OP_FLAG_EXCL = 0x1,
  LIBRADOS_OP_FLAG_FAILOK = 0x2,
  LIBRADOS_OP_FLAG_FADVISE_RANDOM = 0x4,
  LIBRADOS_OP_FLAG_FADVISE_SEQUENTIAL = 0x8,
  LIBRADOS_OP_FLAG_FADVISE_WILLNEED = 0x10,
  LIBRADOS_OP_FLAG_FADVISE_DONTNEED = 0x20,
  LIBRADOS_OP_FLAG_FADVISE_NOCACHE = 0x40,
  LIBRADOS_OP_FLAG_FADVISE_FUA = 0x80,
};
enum {
 LIBRADOS_CMPXATTR_OP_EQ = 1,
 LIBRADOS_CMPXATTR_OP_NE = 2,
 LIBRADOS_CMPXATTR_OP_GT = 3,
 LIBRADOS_CMPXATTR_OP_GTE = 4,
 LIBRADOS_CMPXATTR_OP_LT = 5,
 LIBRADOS_CMPXATTR_OP_LTE = 6
};
enum {
  LIBRADOS_OPERATION_NOFLAG = 0,
  LIBRADOS_OPERATION_BALANCE_READS = 1,
  LIBRADOS_OPERATION_LOCALIZE_READS = 2,
  LIBRADOS_OPERATION_ORDER_READS_WRITES = 4,
  LIBRADOS_OPERATION_IGNORE_CACHE = 8,
  LIBRADOS_OPERATION_SKIPRWLOCKS = 16,
  LIBRADOS_OPERATION_IGNORE_OVERLAY = 32,
  LIBRADOS_OPERATION_FULL_TRY = 64,
  LIBRADOS_OPERATION_FULL_FORCE = 128,
  LIBRADOS_OPERATION_IGNORE_REDIRECT = 256,
  LIBRADOS_OPERATION_ORDERSNAP = 512,
  LIBRADOS_OPERATION_RETURNVEC = 1024,
};
enum {
  LIBRADOS_ALLOC_HINT_FLAG_SEQUENTIAL_WRITE = 1,
  LIBRADOS_ALLOC_HINT_FLAG_RANDOM_WRITE = 2,
  LIBRADOS_ALLOC_HINT_FLAG_SEQUENTIAL_READ = 4,
  LIBRADOS_ALLOC_HINT_FLAG_RANDOM_READ = 8,
  LIBRADOS_ALLOC_HINT_FLAG_APPEND_ONLY = 16,
  LIBRADOS_ALLOC_HINT_FLAG_IMMUTABLE = 32,
  LIBRADOS_ALLOC_HINT_FLAG_SHORTLIVED = 64,
  LIBRADOS_ALLOC_HINT_FLAG_LONGLIVED = 128,
  LIBRADOS_ALLOC_HINT_FLAG_COMPRESSIBLE = 256,
  LIBRADOS_ALLOC_HINT_FLAG_INCOMPRESSIBLE = 512,
};
typedef enum {
 LIBRADOS_CHECKSUM_TYPE_XXHASH32 = 0,
 LIBRADOS_CHECKSUM_TYPE_XXHASH64 = 1,
 LIBRADOS_CHECKSUM_TYPE_CRC32C = 2
} rados_checksum_type_t;
typedef void *rados_t;
typedef void *rados_config_t;
typedef void *rados_ioctx_t;
typedef void *rados_list_ctx_t;
typedef void * rados_object_list_cursor;
typedef struct {
  size_t oid_length;
  char *oid;
  size_t nspace_length;
  char *nspace;
  size_t locator_length;
  char *locator;
} rados_object_list_item;
typedef uint64_t rados_snap_t;
typedef void *rados_xattrs_iter_t;
typedef void *rados_omap_iter_t;
struct rados_pool_stat_t {
  uint64_t num_bytes;
  uint64_t num_kb;
  uint64_t num_objects;
  uint64_t num_object_clones;
  uint64_t num_object_copies;
  uint64_t num_objects_missing_on_primary;
  uint64_t num_objects_unfound;
  uint64_t num_objects_degraded;
  uint64_t num_rd;
  uint64_t num_rd_kb;
  uint64_t num_wr;
  uint64_t num_wr_kb;
  uint64_t num_user_bytes;
  uint64_t compressed_bytes_orig;
  uint64_t compressed_bytes;
  uint64_t compressed_bytes_alloc;
};
struct rados_cluster_stat_t {
  uint64_t kb;
  uint64_t kb_used;
  uint64_t kb_avail;
  uint64_t num_objects;
};
typedef void *rados_write_op_t;
typedef void *rados_read_op_t;
typedef void *rados_completion_t;
struct blkin_trace_info;
 void rados_version(int *major, int *minor, int *extra);
 int rados_create(rados_t *cluster, const char * const id);
 int rados_create2(rados_t *pcluster,
                                 const char *const clustername,
                                 const char * const name, uint64_t flags);
 int rados_create_with_context(rados_t *cluster,
                                             rados_config_t cct);
 int rados_ping_monitor(rados_t cluster, const char *mon_id,
                                      char **outstr, size_t *outstrlen);
 int rados_connect(rados_t cluster);
 void rados_shutdown(rados_t cluster);
 int rados_conf_read_file(rados_t cluster, const char *path);
 int rados_conf_parse_argv(rados_t cluster, int argc,
                                         const char **argv);
 int rados_conf_parse_argv_remainder(rados_t cluster, int argc,
                       const char **argv,
                                                   const char **remargv);
 int rados_conf_parse_env(rados_t cluster, const char *var);
 int rados_conf_set(rados_t cluster, const char *option,
                                  const char *value);
 int rados_conf_get(rados_t cluster, const char *option,
                                  char *buf, size_t len);
 int rados_cluster_stat(rados_t cluster,
                                      struct rados_cluster_stat_t *result);
 int rados_cluster_fsid(rados_t cluster, char *buf, size_t len);
 int rados_wait_for_latest_osdmap(rados_t cluster);
 int rados_pool_list(rados_t cluster, char *buf, size_t len);
 int rados_inconsistent_pg_list(rados_t cluster, int64_t pool,
           char *buf, size_t len);
 rados_config_t rados_cct(rados_t cluster);
 uint64_t rados_get_instance_id(rados_t cluster);
 int rados_get_min_compatible_osd(rados_t cluster,
                                                int8_t* require_osd_release);
 int rados_get_min_compatible_client(rados_t cluster,
                                                   int8_t* min_compat_client,
                                                   int8_t* require_min_compat_client);
 int rados_ioctx_create(rados_t cluster, const char *pool_name,
                                      rados_ioctx_t *ioctx);
 int rados_ioctx_create2(rados_t cluster, int64_t pool_id,
                                       rados_ioctx_t *ioctx);
 void rados_ioctx_destroy(rados_ioctx_t io);
 rados_config_t rados_ioctx_cct(rados_ioctx_t io);
 rados_t rados_ioctx_get_cluster(rados_ioctx_t io);
 int rados_ioctx_pool_stat(rados_ioctx_t io,
                                         struct rados_pool_stat_t *stats);
 int64_t rados_pool_lookup(rados_t cluster,
                                         const char *pool_name);
 int rados_pool_reverse_lookup(rados_t cluster, int64_t id,
                                             char *buf, size_t maxlen);
 int rados_pool_create(rados_t cluster, const char *pool_name);
 int rados_pool_create_with_auid(rados_t cluster,
                                               const char *pool_name,
                                               uint64_t auid)
  __attribute__((deprecated));
 int rados_pool_create_with_crush_rule(rados_t cluster,
                                                     const char *pool_name,
                         uint8_t crush_rule_num);
 int rados_pool_create_with_all(rados_t cluster,
                                              const char *pool_name,
                                              uint64_t auid,
                         uint8_t crush_rule_num)
  __attribute__((deprecated));
 int rados_pool_get_base_tier(rados_t cluster, int64_t pool,
                                            int64_t* base_tier);
 int rados_pool_delete(rados_t cluster, const char *pool_name);
 int rados_ioctx_pool_set_auid(rados_ioctx_t io, uint64_t auid)
  __attribute__((deprecated));
 int rados_ioctx_pool_get_auid(rados_ioctx_t io, uint64_t *auid)
  __attribute__((deprecated));
 int rados_ioctx_pool_requires_alignment(rados_ioctx_t io)
  __attribute__((deprecated));
 int rados_ioctx_pool_requires_alignment2(rados_ioctx_t io,
  int *req);
 uint64_t rados_ioctx_pool_required_alignment(rados_ioctx_t io)
  __attribute__((deprecated));
 int rados_ioctx_pool_required_alignment2(rados_ioctx_t io,
  uint64_t *alignment);
 int64_t rados_ioctx_get_id(rados_ioctx_t io);
 int rados_ioctx_get_pool_name(rados_ioctx_t io, char *buf,
                                             unsigned maxlen);
 void rados_ioctx_locator_set_key(rados_ioctx_t io,
                                                const char *key);
 void rados_ioctx_set_namespace(rados_ioctx_t io,
                                              const char *nspace);
 int rados_ioctx_get_namespace(rados_ioctx_t io, char *buf,
                                             unsigned maxlen);
 int rados_nobjects_list_open(rados_ioctx_t io,
                                            rados_list_ctx_t *ctx);
 uint32_t rados_nobjects_list_get_pg_hash_position(rados_list_ctx_t ctx);
 uint32_t rados_nobjects_list_seek(rados_list_ctx_t ctx,
                                                 uint32_t pos);
 uint32_t rados_nobjects_list_seek_cursor(rados_list_ctx_t ctx,
                                                        rados_object_list_cursor cursor);
 int rados_nobjects_list_get_cursor(rados_list_ctx_t ctx,
                                                  rados_object_list_cursor *cursor);
 int rados_nobjects_list_next(rados_list_ctx_t ctx,
                                            const char **entry,
                                     const char **key,
                                            const char **nspace);
 int rados_nobjects_list_next2(rados_list_ctx_t ctx,
                                             const char **entry,
                                             const char **key,
                                             const char **nspace,
                                             size_t *entry_size,
                                             size_t *key_size,
                                             size_t *nspace_size);
 void rados_nobjects_list_close(rados_list_ctx_t ctx);
 rados_object_list_cursor rados_object_list_begin(
  rados_ioctx_t io);
 rados_object_list_cursor rados_object_list_end(rados_ioctx_t io);
 int rados_object_list_is_end(rados_ioctx_t io,
    rados_object_list_cursor cur);
 void rados_object_list_cursor_free(rados_ioctx_t io,
    rados_object_list_cursor cur);
 int rados_object_list_cursor_cmp(rados_ioctx_t io,
    rados_object_list_cursor lhs, rados_object_list_cursor rhs);
 int rados_object_list(rados_ioctx_t io,
    const rados_object_list_cursor start,
    const rados_object_list_cursor finish,
    const size_t result_size,
    const char *filter_buf,
    const size_t filter_buf_len,
    rados_object_list_item *results,
    rados_object_list_cursor *next);
 void rados_object_list_free(
    const size_t result_size,
    rados_object_list_item *results);
 void rados_object_list_slice(rados_ioctx_t io,
    const rados_object_list_cursor start,
    const rados_object_list_cursor finish,
    const size_t n,
    const size_t m,
    rados_object_list_cursor *split_start,
    rados_object_list_cursor *split_finish);
 int rados_ioctx_snap_create(rados_ioctx_t io,
                                           const char *snapname);
 int rados_ioctx_snap_remove(rados_ioctx_t io,
                                           const char *snapname);
 int rados_ioctx_snap_rollback(rados_ioctx_t io, const char *oid,
                               const char *snapname);
 int rados_rollback(rados_ioctx_t io, const char *oid,
      const char *snapname)
  __attribute__((deprecated));
 void rados_ioctx_snap_set_read(rados_ioctx_t io,
                                              rados_snap_t snap);
 int rados_ioctx_selfmanaged_snap_create(rados_ioctx_t io,
                                                       rados_snap_t *snapid);
 void
rados_aio_ioctx_selfmanaged_snap_create(rados_ioctx_t io,
                                        rados_snap_t *snapid,
                                        rados_completion_t completion);
 int rados_ioctx_selfmanaged_snap_remove(rados_ioctx_t io,
                                                       rados_snap_t snapid);
 void
rados_aio_ioctx_selfmanaged_snap_remove(rados_ioctx_t io,
                                        rados_snap_t snapid,
                                        rados_completion_t completion);
 int rados_ioctx_selfmanaged_snap_rollback(rados_ioctx_t io,
                                                         const char *oid,
                                                         rados_snap_t snapid);
 int rados_ioctx_selfmanaged_snap_set_write_ctx(rados_ioctx_t io,
                                                              rados_snap_t seq,
                                                              rados_snap_t *snaps,
                                                              int num_snaps);
 int rados_ioctx_snap_list(rados_ioctx_t io, rados_snap_t *snaps,
                                         int maxlen);
 int rados_ioctx_snap_lookup(rados_ioctx_t io, const char *name,
                                           rados_snap_t *id);
 int rados_ioctx_snap_get_name(rados_ioctx_t io, rados_snap_t id,
                                             char *name, int maxlen);
 int rados_ioctx_snap_get_stamp(rados_ioctx_t io, rados_snap_t id,
                                              time_t *t);
 uint64_t rados_get_last_version(rados_ioctx_t io);
 int rados_write(rados_ioctx_t io, const char *oid,
                               const char *buf, size_t len, uint64_t off);
 int rados_write_full(rados_ioctx_t io, const char *oid,
                                    const char *buf, size_t len);
 int rados_writesame(rados_ioctx_t io, const char *oid,
                                   const char *buf, size_t data_len,
                                   size_t write_len, uint64_t off);
 int rados_append(rados_ioctx_t io, const char *oid,
                                const char *buf, size_t len);
 int rados_read(rados_ioctx_t io, const char *oid, char *buf,
                              size_t len, uint64_t off);
 int rados_checksum(rados_ioctx_t io, const char *oid,
      rados_checksum_type_t type,
      const char *init_value, size_t init_value_len,
      size_t len, uint64_t off, size_t chunk_size,
      char *pchecksum, size_t checksum_len);
 int rados_remove(rados_ioctx_t io, const char *oid);
 int rados_trunc(rados_ioctx_t io, const char *oid,
                               uint64_t size);
 int rados_cmpext(rados_ioctx_t io, const char *o,
                                const char *cmp_buf, size_t cmp_len,
                                uint64_t off);
 int rados_getxattr(rados_ioctx_t io, const char *o,
                                  const char *name, char *buf, size_t len);
 int rados_setxattr(rados_ioctx_t io, const char *o,
                                  const char *name, const char *buf,
                                  size_t len);
 int rados_rmxattr(rados_ioctx_t io, const char *o,
                                 const char *name);
 int rados_getxattrs(rados_ioctx_t io, const char *oid,
                                   rados_xattrs_iter_t *iter);
 int rados_getxattrs_next(rados_xattrs_iter_t iter,
                                        const char **name, const char **val,
                                        size_t *len);
 void rados_getxattrs_end(rados_xattrs_iter_t iter);
 int rados_omap_get_next(rados_omap_iter_t iter,
                                       char **key,
                                       char **val,
                                       size_t *len);
 int rados_omap_get_next2(rados_omap_iter_t iter,
                                       char **key,
                                       char **val,
                                       size_t *key_len,
                                       size_t *val_len);
 unsigned int rados_omap_iter_size(rados_omap_iter_t iter);
 void rados_omap_get_end(rados_omap_iter_t iter);
 int rados_stat(rados_ioctx_t io, const char *o, uint64_t *psize,
                              time_t *pmtime);
 int rados_stat2(rados_ioctx_t io, const char *o, uint64_t *psize,
                              struct timespec *pmtime);
 int rados_exec(rados_ioctx_t io, const char *oid,
                              const char *cls, const char *method,
                       const char *in_buf, size_t in_len, char *buf,
                              size_t out_len);
typedef void (*rados_callback_t)(rados_completion_t cb, void *arg);
 int rados_aio_create_completion(void *cb_arg,
                                               rados_callback_t cb_complete,
                                               rados_callback_t cb_safe,
                   rados_completion_t *pc);
 int rados_aio_create_completion2(void *cb_arg,
      rados_callback_t cb_complete,
      rados_completion_t *pc);
 int rados_aio_wait_for_complete(rados_completion_t c);
 int rados_aio_wait_for_safe(rados_completion_t c)
  __attribute__((deprecated));
 int rados_aio_is_complete(rados_completion_t c);
 int rados_aio_is_safe(rados_completion_t c);
 int rados_aio_wait_for_complete_and_cb(rados_completion_t c);
 int rados_aio_wait_for_safe_and_cb(rados_completion_t c)
  __attribute__((deprecated));
 int rados_aio_is_complete_and_cb(rados_completion_t c);
 int rados_aio_is_safe_and_cb(rados_completion_t c);
 int rados_aio_get_return_value(rados_completion_t c);
 uint64_t rados_aio_get_version(rados_completion_t c);
 void rados_aio_release(rados_completion_t c);
 int rados_aio_write(rados_ioctx_t io, const char *oid,
                     rados_completion_t completion,
                     const char *buf, size_t len, uint64_t off);
 int rados_aio_append(rados_ioctx_t io, const char *oid,
                      rados_completion_t completion,
                      const char *buf, size_t len);
 int rados_aio_write_full(rados_ioctx_t io, const char *oid,
                   rados_completion_t completion,
                   const char *buf, size_t len);
 int rados_aio_writesame(rados_ioctx_t io, const char *oid,
                  rados_completion_t completion,
                  const char *buf, size_t data_len,
           size_t write_len, uint64_t off);
 int rados_aio_remove(rados_ioctx_t io, const char *oid,
                      rados_completion_t completion);
 int rados_aio_read(rados_ioctx_t io, const char *oid,
                    rados_completion_t completion,
                    char *buf, size_t len, uint64_t off);
 int rados_aio_flush(rados_ioctx_t io);
 int rados_aio_flush_async(rados_ioctx_t io,
                                         rados_completion_t completion);
 int rados_aio_stat(rados_ioctx_t io, const char *o,
                    rados_completion_t completion,
                    uint64_t *psize, time_t *pmtime);
 int rados_aio_stat2(rados_ioctx_t io, const char *o,
                    rados_completion_t completion,
                    uint64_t *psize, struct timespec *pmtime);
 int rados_aio_cmpext(rados_ioctx_t io, const char *o,
                                    rados_completion_t completion,
                                    const char *cmp_buf,
                                    size_t cmp_len,
                                    uint64_t off);
 int rados_aio_cancel(rados_ioctx_t io,
                                    rados_completion_t completion);
 int rados_aio_exec(rados_ioctx_t io, const char *o,
      rados_completion_t completion,
      const char *cls, const char *method,
      const char *in_buf, size_t in_len,
      char *buf, size_t out_len);
 int rados_aio_getxattr(rados_ioctx_t io, const char *o,
          rados_completion_t completion,
          const char *name, char *buf, size_t len);
 int rados_aio_setxattr(rados_ioctx_t io, const char *o,
          rados_completion_t completion,
          const char *name, const char *buf,
          size_t len);
 int rados_aio_rmxattr(rados_ioctx_t io, const char *o,
         rados_completion_t completion,
         const char *name);
 int rados_aio_getxattrs(rados_ioctx_t io, const char *oid,
           rados_completion_t completion,
           rados_xattrs_iter_t *iter);
typedef void (*rados_watchcb_t)(uint8_t opcode, uint64_t ver, void *arg);
typedef void (*rados_watchcb2_t)(void *arg,
     uint64_t notify_id,
     uint64_t handle,
     uint64_t notifier_id,
     void *data,
     size_t data_len);
  typedef void (*rados_watcherrcb_t)(void *pre, uint64_t cookie, int err);
 int rados_watch(rados_ioctx_t io, const char *o, uint64_t ver,
          uint64_t *cookie,
          rados_watchcb_t watchcb, void *arg)
  __attribute__((deprecated));
 int rados_watch2(rados_ioctx_t io, const char *o, uint64_t *cookie,
    rados_watchcb2_t watchcb,
    rados_watcherrcb_t watcherrcb,
    void *arg);
 int rados_watch3(rados_ioctx_t io, const char *o, uint64_t *cookie,
        rados_watchcb2_t watchcb,
        rados_watcherrcb_t watcherrcb,
        uint32_t timeout,
        void *arg);
 int rados_aio_watch(rados_ioctx_t io, const char *o,
       rados_completion_t completion, uint64_t *handle,
       rados_watchcb2_t watchcb,
       rados_watcherrcb_t watcherrcb,
       void *arg);
 int rados_aio_watch2(rados_ioctx_t io, const char *o,
           rados_completion_t completion, uint64_t *handle,
           rados_watchcb2_t watchcb,
           rados_watcherrcb_t watcherrcb,
           uint32_t timeout,
           void *arg);
 int rados_watch_check(rados_ioctx_t io, uint64_t cookie);
 int rados_unwatch(rados_ioctx_t io, const char *o, uint64_t cookie)
  __attribute__((deprecated));
 int rados_unwatch2(rados_ioctx_t io, uint64_t cookie);
 int rados_aio_unwatch(rados_ioctx_t io, uint64_t cookie,
                                     rados_completion_t completion);
 int rados_notify(rados_ioctx_t io, const char *o, uint64_t ver,
    const char *buf, int buf_len)
  __attribute__((deprecated));
 int rados_aio_notify(rados_ioctx_t io, const char *o,
        rados_completion_t completion,
        const char *buf, int buf_len,
        uint64_t timeout_ms, char **reply_buffer,
        size_t *reply_buffer_len);
 int rados_notify2(rados_ioctx_t io, const char *o,
     const char *buf, int buf_len,
     uint64_t timeout_ms,
     char **reply_buffer, size_t *reply_buffer_len);
 int rados_decode_notify_response(char *reply_buffer, size_t reply_buffer_len,
                                                struct notify_ack_t **acks, size_t *nr_acks,
                                                struct notify_timeout_t **timeouts, size_t *nr_timeouts);
 void rados_free_notify_response(struct notify_ack_t *acks, size_t nr_acks,
                                               struct notify_timeout_t *timeouts);
 int rados_notify_ack(rados_ioctx_t io, const char *o,
        uint64_t notify_id, uint64_t cookie,
        const char *buf, int buf_len);
 int rados_watch_flush(rados_t cluster);
 int rados_aio_watch_flush(rados_t cluster, rados_completion_t completion);
 int rados_cache_pin(rados_ioctx_t io, const char *o);
 int rados_cache_unpin(rados_ioctx_t io, const char *o);
 int rados_set_alloc_hint(rados_ioctx_t io, const char *o,
                                        uint64_t expected_object_size,
                                        uint64_t expected_write_size);
 int rados_set_alloc_hint2(rados_ioctx_t io, const char *o,
      uint64_t expected_object_size,
      uint64_t expected_write_size,
      uint32_t flags);
 rados_write_op_t rados_create_write_op(void);
 void rados_release_write_op(rados_write_op_t write_op);
 void rados_write_op_set_flags(rados_write_op_t write_op,
                                             int flags);
 void rados_write_op_assert_exists(rados_write_op_t write_op);
 void rados_write_op_assert_version(rados_write_op_t write_op, uint64_t ver);
 void rados_write_op_cmpext(rados_write_op_t write_op,
                                          const char *cmp_buf,
                                          size_t cmp_len,
                                          uint64_t off,
                                          int *prval);
 void rados_write_op_cmpxattr(rados_write_op_t write_op,
                                            const char *name,
                                            uint8_t comparison_operator,
                                            const char *value,
                                            size_t value_len);
 void rados_write_op_omap_cmp(rados_write_op_t write_op,
                                            const char *key,
                                            uint8_t comparison_operator,
                                            const char *val,
                                            size_t val_len,
                                            int *prval);
 void rados_write_op_omap_cmp2(rados_write_op_t write_op,
                                            const char *key,
                                            uint8_t comparison_operator,
                                            const char *val,
                                            size_t key_len,
                                            size_t val_len,
                                            int *prval);
 void rados_write_op_setxattr(rados_write_op_t write_op,
                                            const char *name,
                                            const char *value,
                                            size_t value_len);
 void rados_write_op_rmxattr(rados_write_op_t write_op,
                                           const char *name);
 void rados_write_op_create(rados_write_op_t write_op,
                                          int exclusive,
                                          const char* category);
 void rados_write_op_write(rados_write_op_t write_op,
                                         const char *buffer,
                                         size_t len,
                                         uint64_t offset);
 void rados_write_op_write_full(rados_write_op_t write_op,
                                              const char *buffer,
                                              size_t len);
 void rados_write_op_writesame(rados_write_op_t write_op,
                                             const char *buffer,
                                             size_t data_len,
                                             size_t write_len,
                                             uint64_t offset);
 void rados_write_op_append(rados_write_op_t write_op,
                                          const char *buffer,
                                          size_t len);
 void rados_write_op_remove(rados_write_op_t write_op);
 void rados_write_op_truncate(rados_write_op_t write_op,
                                            uint64_t offset);
 void rados_write_op_zero(rados_write_op_t write_op,
                   uint64_t offset,
                   uint64_t len);
 void rados_write_op_exec(rados_write_op_t write_op,
                   const char *cls,
                   const char *method,
                   const char *in_buf,
                   size_t in_len,
                   int *prval);
 void rados_write_op_omap_set(rados_write_op_t write_op,
                                            char const* const* keys,
                                            char const* const* vals,
                                            const size_t *lens,
                                            size_t num);
 void rados_write_op_omap_set2(rados_write_op_t write_op,
                                            char const* const* keys,
                                            char const* const* vals,
                                            const size_t *key_lens,
                                            const size_t *val_lens,
                                            size_t num);
 void rados_write_op_omap_rm_keys(rados_write_op_t write_op,
                                                char const* const* keys,
                                                size_t keys_len);
 void rados_write_op_omap_rm_keys2(rados_write_op_t write_op,
                                                char const* const* keys,
                                                const size_t* key_lens,
                                                size_t keys_len);
 void rados_write_op_omap_rm_range2(rados_write_op_t write_op,
                                                  const char *key_begin,
                                                  size_t key_begin_len,
                                                  const char *key_end,
                                                  size_t key_end_len);
 void rados_write_op_omap_clear(rados_write_op_t write_op);
 void rados_write_op_set_alloc_hint(rados_write_op_t write_op,
                                                  uint64_t expected_object_size,
                                                  uint64_t expected_write_size);
 void rados_write_op_set_alloc_hint2(rados_write_op_t write_op,
         uint64_t expected_object_size,
         uint64_t expected_write_size,
         uint32_t flags);
 int rados_write_op_operate(rados_write_op_t write_op,
                     rados_ioctx_t io,
                     const char *oid,
                     time_t *mtime,
                     int flags);
 int rados_write_op_operate2(rados_write_op_t write_op,
                                           rados_ioctx_t io,
                                           const char *oid,
                                           struct timespec *mtime,
                                           int flags);
 int rados_aio_write_op_operate(rados_write_op_t write_op,
                                              rados_ioctx_t io,
                                              rados_completion_t completion,
                                              const char *oid,
                                              time_t *mtime,
                         int flags);
 int rados_aio_write_op_operate2(rados_write_op_t write_op,
                                               rados_ioctx_t io,
                                               rados_completion_t completion,
                                               const char *oid,
                                               struct timespec *mtime,
                                               int flags);
 rados_read_op_t rados_create_read_op(void);
 void rados_release_read_op(rados_read_op_t read_op);
 void rados_read_op_set_flags(rados_read_op_t read_op, int flags);
 void rados_read_op_assert_exists(rados_read_op_t read_op);
 void rados_read_op_assert_version(rados_read_op_t read_op, uint64_t ver);
 void rados_read_op_cmpext(rados_read_op_t read_op,
                                         const char *cmp_buf,
                                         size_t cmp_len,
                                         uint64_t off,
                                         int *prval);
 void rados_read_op_cmpxattr(rados_read_op_t read_op,
                      const char *name,
                      uint8_t comparison_operator,
                      const char *value,
                      size_t value_len);
 void rados_read_op_getxattrs(rados_read_op_t read_op,
                       rados_xattrs_iter_t *iter,
                       int *prval);
 void rados_read_op_omap_cmp(rados_read_op_t read_op,
                                           const char *key,
                                           uint8_t comparison_operator,
                                           const char *val,
                                           size_t val_len,
                                           int *prval);
 void rados_read_op_omap_cmp2(rados_read_op_t read_op,
                                           const char *key,
                                           uint8_t comparison_operator,
                                           const char *val,
                                           size_t key_len,
                                           size_t val_len,
                                           int *prval);
 void rados_read_op_stat(rados_read_op_t read_op,
                  uint64_t *psize,
                  time_t *pmtime,
                  int *prval);
 void rados_read_op_stat2(rados_read_op_t read_op,
                  uint64_t *psize,
                  struct timespec *pmtime,
                  int *prval);
 void rados_read_op_read(rados_read_op_t read_op,
                  uint64_t offset,
                  size_t len,
                  char *buffer,
                  size_t *bytes_read,
                  int *prval);
 void rados_read_op_checksum(rados_read_op_t read_op,
        rados_checksum_type_t type,
        const char *init_value,
        size_t init_value_len,
        uint64_t offset, size_t len,
        size_t chunk_size, char *pchecksum,
        size_t checksum_len, int *prval);
 void rados_read_op_exec(rados_read_op_t read_op,
                  const char *cls,
                  const char *method,
                  const char *in_buf,
                  size_t in_len,
                  char **out_buf,
                  size_t *out_len,
                  int *prval);
 void rados_read_op_exec_user_buf(rados_read_op_t read_op,
                    const char *cls,
                    const char *method,
                    const char *in_buf,
                    size_t in_len,
                    char *out_buf,
                    size_t out_len,
                    size_t *used_len,
                    int *prval);
 void rados_read_op_omap_get_vals(rados_read_op_t read_op,
                    const char *start_after,
                    const char *filter_prefix,
                    uint64_t max_return,
                    rados_omap_iter_t *iter,
                    int *prval)
  __attribute__((deprecated));
 void rados_read_op_omap_get_vals2(rados_read_op_t read_op,
       const char *start_after,
       const char *filter_prefix,
       uint64_t max_return,
       rados_omap_iter_t *iter,
       unsigned char *pmore,
       int *prval);
 void rados_read_op_omap_get_keys(rados_read_op_t read_op,
                    const char *start_after,
                    uint64_t max_return,
                    rados_omap_iter_t *iter,
                    int *prval)
  __attribute__((deprecated));
 void rados_read_op_omap_get_keys2(rados_read_op_t read_op,
       const char *start_after,
       uint64_t max_return,
       rados_omap_iter_t *iter,
       unsigned char *pmore,
       int *prval);
 void rados_read_op_omap_get_vals_by_keys(rados_read_op_t read_op,
                                                        char const* const* keys,
                                                        size_t keys_len,
                                                        rados_omap_iter_t *iter,
                                                        int *prval);
 void rados_read_op_omap_get_vals_by_keys2(rados_read_op_t read_op,
                                                        char const* const* keys,
                                                        size_t num_keys,
                                                        const size_t* key_lens,
                                                        rados_omap_iter_t *iter,
                                                        int *prval);
 int rados_read_op_operate(rados_read_op_t read_op,
                    rados_ioctx_t io,
                    const char *oid,
                    int flags);
 int rados_aio_read_op_operate(rados_read_op_t read_op,
                        rados_ioctx_t io,
                        rados_completion_t completion,
                        const char *oid,
                        int flags);
 int rados_lock_exclusive(rados_ioctx_t io, const char * oid,
                                        const char * name, const char * cookie,
                                        const char * desc,
                                        struct timeval * duration,
                                        uint8_t flags);
 int rados_lock_shared(rados_ioctx_t io, const char * o,
                                     const char * name, const char * cookie,
                                     const char * tag, const char * desc,
                              struct timeval * duration, uint8_t flags);
 int rados_unlock(rados_ioctx_t io, const char *o,
                                const char *name, const char *cookie);
 int rados_aio_unlock(rados_ioctx_t io, const char *o,
                                    const char *name, const char *cookie,
               rados_completion_t completion);
 ssize_t rados_list_lockers(rados_ioctx_t io, const char *o,
                     const char *name, int *exclusive,
                     char *tag, size_t *tag_len,
                     char *clients, size_t *clients_len,
                     char *cookies, size_t *cookies_len,
                     char *addrs, size_t *addrs_len);
 int rados_break_lock(rados_ioctx_t io, const char *o,
                                    const char *name, const char *client,
                                    const char *cookie);
 int rados_blocklist_add(rados_t cluster,
           char *client_address,
           uint32_t expire_seconds);
 int rados_blacklist_add(rados_t cluster,
           char *client_address,
           uint32_t expire_seconds)
  __attribute__((deprecated));
 int rados_getaddrs(rados_t cluster, char** addrs);
 void rados_set_osdmap_full_try(rados_ioctx_t io)
  __attribute__((deprecated));
 void rados_unset_osdmap_full_try(rados_ioctx_t io)
  __attribute__((deprecated));
 void rados_set_pool_full_try(rados_ioctx_t io);
 void rados_unset_pool_full_try(rados_ioctx_t io);
 int rados_application_enable(rados_ioctx_t io,
                                            const char *app_name, int force);
 int rados_application_list(rados_ioctx_t io, char *values,
                                          size_t *values_len);
 int rados_application_metadata_get(rados_ioctx_t io,
                                                  const char *app_name,
                                                  const char *key, char *value,
                                                  size_t *value_len);
 int rados_application_metadata_set(rados_ioctx_t io,
                                                  const char *app_name,
                                                  const char *key,
                                                  const char *value);
 int rados_application_metadata_remove(rados_ioctx_t io,
                                                     const char *app_name,
                                                     const char *key);
 int rados_application_metadata_list(rados_ioctx_t io,
                                                   const char *app_name,
                                                   char *keys, size_t *key_len,
                                                   char *values,
                                                   size_t *vals_len);
 int rados_mon_command(rados_t cluster, const char **cmd,
                                     size_t cmdlen, const char *inbuf,
                                     size_t inbuflen, char **outbuf,
                                     size_t *outbuflen, char **outs,
                                     size_t *outslen);
 int rados_mgr_command(rados_t cluster, const char **cmd,
                                     size_t cmdlen, const char *inbuf,
                                     size_t inbuflen, char **outbuf,
                                     size_t *outbuflen, char **outs,
                                     size_t *outslen);
 int rados_mgr_command_target(
  rados_t cluster,
  const char *name,
  const char **cmd,
  size_t cmdlen, const char *inbuf,
  size_t inbuflen, char **outbuf,
  size_t *outbuflen, char **outs,
  size_t *outslen);
 int rados_mon_command_target(rados_t cluster, const char *name,
                       const char **cmd, size_t cmdlen,
                       const char *inbuf, size_t inbuflen,
                       char **outbuf, size_t *outbuflen,
                       char **outs, size_t *outslen);
 void rados_buffer_free(char *buf);
 int rados_osd_command(rados_t cluster, int osdid,
                                     const char **cmd, size_t cmdlen,
                       const char *inbuf, size_t inbuflen,
                       char **outbuf, size_t *outbuflen,
                       char **outs, size_t *outslen);
 int rados_pg_command(rados_t cluster, const char *pgstr,
                                    const char **cmd, size_t cmdlen,
                      const char *inbuf, size_t inbuflen,
                      char **outbuf, size_t *outbuflen,
                      char **outs, size_t *outslen);
typedef void (*rados_log_callback_t)(void *arg,
         const char *line,
         const char *who,
         uint64_t sec, uint64_t nsec,
         uint64_t seq, const char *level,
         const char *msg);
typedef void (*rados_log_callback2_t)(void *arg,
         const char *line,
         const char *channel,
         const char *who,
         const char *name,
         uint64_t sec, uint64_t nsec,
         uint64_t seq, const char *level,
         const char *msg);
 int rados_monitor_log(rados_t cluster, const char *level,
                                     rados_log_callback_t cb, void *arg);
 int rados_monitor_log2(rados_t cluster, const char *level,
          rados_log_callback2_t cb, void *arg);
 int rados_service_register(
  rados_t cluster,
  const char *service,
  const char *daemon,
  const char *metadata_dict);
 int rados_service_update_status(
  rados_t cluster,
  const char *status_dict);
 int rados_objects_list_open(
  rados_ioctx_t io,
  rados_list_ctx_t *ctx) __attribute__((deprecated));
 uint32_t rados_objects_list_get_pg_hash_position(
  rados_list_ctx_t ctx) __attribute__((deprecated));
 uint32_t rados_objects_list_seek(
  rados_list_ctx_t ctx,
  uint32_t pos) __attribute__((deprecated));
 int rados_objects_list_next(
  rados_list_ctx_t ctx,
  const char **entry,
  const char **key) __attribute__((deprecated));
 void rados_objects_list_close(
  rados_list_ctx_t ctx) __attribute__((deprecated));
