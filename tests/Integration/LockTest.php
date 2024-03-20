<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\Pool\Object\Lock\ForeignLock;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\TimeValue;
use Tests\RadosTestCase;

class LockTest extends RadosTestCase
{
    public function testLockRenew(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-lock-object")->writeFull("test-data");
        $lock = $object->createExclusiveLock("testLockRenew", duration: new TimeValue(0, 500000));
        // Renew valid lock
        $lock->renew();
        sleep(1);
        // Renew expired lock
        $lock->renew();
        sleep(1);
        // Renew expired lock fails with mustRenew
        $this->expectExceptionCode(-Errno::ENOENT->value);
        $lock->renew(mustRenew: true);
    }

    public function testSharedLockPreventsExclusiveLock(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-lock-object1")->writeFull("test-data");
        $sharedLock = $object->createSharedLock("testSharedLockPreventsExclusiveLock", duration: new TimeValue(5));
        $this->expectExceptionCode(-Errno::EBUSY->value);
        $object->createExclusiveLock("testSharedLockPreventsExclusiveLock");
    }

    public function testExclusiveLockPreventsSharedLock(): void
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-lock-object2")->writeFull("test-data");
        $lock = $object->createExclusiveLock("testExclusiveLockPreventsSharedLock", duration: new TimeValue(5));
        $this->expectExceptionCode(-Errno::EBUSY->value);
        $object->createSharedLock("testExclusiveLockPreventsSharedLock");
    }

    public function testListLocks()
    {
        $ioContext = $this->getIOContext();
        $object = $ioContext->getObject("test-lock-object3")->writeFull("test-data");

        $lock1 = $object->createSharedLock("testListLocks", duration: new TimeValue(5));
        $lock2 = $object->createSharedLock("testListLocks", duration: new TimeValue(5));
        $lock2 = $object->createSharedLock("testListLocks", duration: new TimeValue(5));

        $locks = $object->listLocks("testListLocks");
        $this->assertCount(3, $locks);

        foreach ($locks as $lock) {
            $this->assertInstanceOf(ForeignLock::class, $lock);
            $lock->break();
        }

        $object->createExclusiveLock("testListLocks", duration: new TimeValue(0, 500000));
    }
}
