# php-rados-ffi
An object oriented PHP library for using librados with FFI.

## Requirements
- Linux
- PHP 8.1 or later
- php-ffi
- librados

## Installation
```bash
composer require aternos/rados-ffi
```

## Usage

Before the library can be used, the librados shared library must be loaded.
This can be done using the `initialize()` method of the [`Rados`](src/Rados.php) class.
```php
$rados = \Aternos\Rados\Rados::getInstance()->initialize();
```

### Preloading

To preload librados, ensure that FFI preloading is enabled and add the following line to your `opcache.preload` file:
```php
\Aternos\Rados\Rados::getInstance()->preload();
```

Rados can then be initialized by calling `initializePreloaded()` instead of `initialize()`.

```php
$rados = \Aternos\Rados\Rados::getInstance()->initializePreloaded();
```

### Cluster

The [`Rados`](src/Rados.php) instance can then be used to create a [`Cluster`](src/Cluster/Cluster.php) instance, 
which is used to connect to a Ceph cluster.

```php
$cluster = $rados->createCluster();
$cluster->configReadFile('/etc/ceph/ceph.conf');
$cluster->connect();
```

Once connected, a [`Cluster`](src/Cluster/Cluster.php) object can be used to perform operations and 
request information about the cluster.
It is, for example, possible to ping monitors, request the cluster's ID, or list pools.

```php
var_dump($cluster->pingMonitor("mon1"));
var_dump($cluster->getClusterFsid());

foreach ($cluster->getPools() as $pool) {
    echo $pool->getName() . PHP_EOL;
}
```

### Pool

[`Pool`](src/Cluster/Pool/Pool.php) objects contain general information about a pool, and can be used to an [`IOContext`](src/Cluster/Pool/IOContext.php).
```php
$ioContext = $pool->createIOContext();
```

### IOContext

[`IOContext`](src/Cluster/Pool/IOContext.php) objects are used to perform operations on a pool. 
It can, for example, be used to iterate over objects in the pool, or to get a specific object.

```php
foreach ($ioContext->createObjectIterator() as $entry) {
    echo $entry->getObject()->getId() . PHP_EOL;
}

$object = $ioContext->getObject("object1");
```

### RadosObject

A `RadosObject` represents an object in a Ceph pool, 
and can be used to read and write data or modify attributes.

```php
// Write full object
$object->writeFull("Hello, World");

// Write at offset
$object->write("World", 7);

//Append to object
$object->append("!");

// Read from object
echo $object->read(13, 0) . PHP_EOL;
```

### Async operations and completions

Many IO operations can be performed asynchronously. Asynchronous operations return a
[`OperationCompletion`](src/Completion/OperationCompletion.php) object, which can be 
used to track the status of the operation and to wait for its completion.

```php
$completion = $object->writeFullAsync("Hello, World");
```

To check the status of a completion, the `isComplete()` method can be used.
```php
if ($completion->isComplete()) {
    echo "Operation is complete" . PHP_EOL;
}
```

It is also possible to block until the operation is complete using the `waitForComplete()` method.
```php
$completion->waitForComplete();
$completion->isComplete(); // true
```

The result of the operation can be obtained using the `getResult()` method. The type
of the result depends on the operation that was performed.
```php
$result = $completion->getResult();
```

If the operation failed, the `getResult()` method will throw an exception.

Likewise, an exception will be thrown if the operation was not completed yet.
To avoid this, `waitAndGetResult()` can be used to block until the operation 
is complete and to get the result.
```php
$result = $completion->waitAndGetResult();
```

### Object operations

[Object operations](https://docs.ceph.com/en/latest/rados/api/librados/#breathe-section-title-object-operations) allow 
performing multiple tasks on an object atomically. Write and read operations can be created by calling 
`$rados->createWriteOperation()` and `$rados->createReadOperation()` respectively.

```php
$object = $ioContext->getObject("object1");

$operation = $rados->createWriteOperation();
$operation->addTask(new \Aternos\Rados\Operation\Common\Task\AssertExistsTask());
$operation->addTask(new \Aternos\Rados\Operation\Write\Task\AppendTask("Hello, World"));
$operation->operate($object);
```

Some tasks, especially in read operations, will return data. This data can be accessed by
calling `getResult()` on the task object after the operation has been completed.

If the task failed, `getResult()` may throw an exception.

```php
$object = $ioContext->getObject("object1");
$object->writeFull("Hello, World");

$operation = $rados->createReadOperation();
$task = new \Aternos\Rados\Operation\Read\Task\ReadTask(0, 12);
$operation->addTask($task);
$operation->operate($object);

echo $task->getResult() . PHP_EOL;
```

If a single task in an operation fails, the entire operation will fail.
This can be avoided by adding the `OperationTaskFlag::FailOK` flag to tasks that are allowed to fail.

```php
$task = new \Aternos\Rados\Operation\Common\Task\CompareExtTask("Hello_", 0);
$task->setFlags([\Aternos\Rados\Constants\OperationTaskFlag::FailOK]);
```

Operations can also be executed asynchronously, using the `operateAsync()` method.

#### Available tasks

##### Common

- `AssertExistsTask`
- `AssertVersionTask`
- `CompareExtTask`
- `CompareXAttributeTask`
- `OMapCompareTask`

##### Read

- `ChecksumTask`
- `ExecuteTask` (with output data)
- `GetXAttributesTask`
- `OMapGetByKeysTask`
- `OMapGetKeysTask`
- `OMapGetTask`
- `ReadTask`
- `StatTask`

##### Write

- `AppendTask`
- `CreateObjectTask`
- `ExecuteTask` (without output data)
- `OMapClearTask`
- `OMapRemoveKeyRangeTask`
- `OMapRemoveKeysTask`
- `OMapSetTask`
- `RemoveTask`
- `RemoveXAttributeTask`
- `SetAllocHintTask`
- `SetXAttributeTask`
- `TruncateTask`
- `WriteFullTask`
- `WriteSameTask`
- `WriteTask`
- `ZeroTask`

### Exceptions and error handling

If a Rados operation fails, it will throw a [`RadosException`](src/Exception/RadosException.php)
The error code returned from librados can be obtained using the `getCode()` method.

To check whether an error has a specific error code, the `is()` method can be used.

```php
try {
    $cluster->getPool("nonexistent")->createIOContext();
} catch (\Aternos\Rados\Exception\RadosException $e) {
    if ($e->is(\Aternos\Rados\Generated\Errno::ENOENT)) {
        echo "Pool does not exist" . PHP_EOL;
    }
}
```

### Available Rados features
This library aims to implement the full librados API. 
There are, however, some features can't be implemented due to limitations in PHP's FFI system.

#### Not planned
- Callback functions for completions
- Watch/Notify
- Log callbacks

PHP callback functions can be passed to C functions using FFI,
but they can only be called (more or less) safely from the main thread.
Since both completions and watches can be called from any thread,
using PHP callback functions is not feasible.

#### Implemented, but not really supported
Some librados features are poorly documented to a point where I do not understand what they are supposed to do.
These features have bindings in this librery, but I can't guarantee that they work as intended.
Currently, this includes:
- Self-managed snapshots
- rados_(un)set_pool_full_try

### How to not segfault

The library uses FFI to call into the librados shared library. 
To avoid crashes from incorrect usage of librados, only call methods and constructors 
that are `public` and not marked as `@internal` in the source code.

Methods marked as `@internal` are not part of the public API and should not be called directly.
