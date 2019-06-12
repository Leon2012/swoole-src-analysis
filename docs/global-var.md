# 全局变量介绍

## SwooleG
最主要的全局变量，里面保存swoole运行的主要信息

```
typedef struct
{
    swTimer timer;

    uint8_t running :1;              //扩展是否已运行
    uint8_t enable_coroutine :1;
    uint8_t use_signalfd :1;
    uint8_t enable_signalfd :1;
    uint8_t reuse_port :1;
    uint8_t socket_dontwait :1;
    uint8_t dns_lookup_random :1;
    uint8_t use_async_resolver :1;

    int error;
    int process_type;
    pid_t pid;

    int signal_alarm;  //for timer with message queue
    int signal_fd;
    int log_fd;
    int null_fd;

    /**
     * worker(worker and task_worker) process chroot / user / group
     */
    char *chroot;
    char *user;
    char *group;

    uint32_t log_level;
    char *log_file;
    uint32_t trace_flags;

    void (*write_log)(int level, char *content, size_t len);
    void (*fatal_error)(int code, const char *str, ...);

    uint16_t cpu_num;

    uint32_t pagesize;
    uint32_t max_sockets;

#ifndef _WIN32
    struct utsname uname;
#endif

    /**
     * tcp socket default buffer size
     */
    uint32_t socket_buffer_size;

    swServer *serv;

    swMemoryPool *memory_pool;        //全局内存池，swoole启动的时候会初始化一块共享内存用于实现进程间共享内存，例如Atomic模块就是基于此共享内存实现
    swReactor *main_reactor;
    swReactor *origin_main_reactor;

    char *task_tmpdir;
    uint16_t task_tmpdir_len;

    char *dns_server_v4;
    char *dns_server_v6;
    double dns_cache_refresh_time;

    swLock lock;
    swHashMap *functions;
    swLinkedList *hooks[SW_MAX_HOOK_TYPE];

} swGlobal_t;


//共享内存大小
#define SW_GLOBAL_MEMORY_PAGESIZE  (2*1024*1024) // global memory page

//init global shared memory
//初始化共享内存，共享内存实现是基于mmap
//swMemoryGlobal_new的第二上参数表示是否初始化共享内存，0 => sw_malloc , 1 => sw_shm_malloc
SwooleG.memory_pool = swMemoryGlobal_new(SW_GLOBAL_MEMORY_PAGESIZE, 1);



```

## SwooleGS

```
typedef struct
{
    swLock lock;
    swLock lock_2;
} swGlobalS_t;


//初始化swGlobalS_t结构，调用 swMemoryGlobal_alloc方法实现
//此变量保存在memory_pool里面，所有进程可以共享此变量
SwooleGS = SwooleG.memory_pool->alloc(SwooleG.memory_pool, sizeof(swGlobalS_t));

//init global lock
//创建memory锁
//pthread_mutexattr_init 初始化锁
//pthread_mutexattr_setpshared(&attr, PTHREAD_PROCESS_SHARED); 实现了进程间同步锁 
swMutex_create(&SwooleGS->lock, 1);   
swMutex_create(&SwooleGS->lock_2, 1);
swMutex_create(&SwooleG.lock, 0);


```


## SwooleTG
```
//Worker process global Variable
typedef struct
{
    /**
     * Always run
     */
    uint8_t run_always;

    /**
     * Current Proccess Worker's id
     */
    uint32_t id;

    /**
     * pipe_worker
     */
    int pipe_used;

    uint32_t reactor_wait_onexit :1;
    uint32_t reactor_init :1;
    uint32_t reactor_ready :1;
    uint32_t reactor_exit :1;
    uint32_t in_client :1;
    uint32_t shutdown :1;
    uint32_t wait_exit :1;

    int max_request;

    swString **buffer_input;
    swString **buffer_output;
    swWorker *worker;
    time_t exit_time;

} swWorkerGlobal_t;
```

## SwooleWG

```
typedef struct
{
    uint16_t id;
    uint8_t type;
    uint8_t update_time;
    swString *buffer_stack;
    swReactor *reactor;
} swThreadGlobal_t;

```