<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\Filesystem\Tests;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class FilesystemCachePoolTest extends \PHPUnit_Framework_TestCase
{
    use CreatePoolTrait;

    /**
     * @var FilesystemCachePool
     */
    private $cache;


    protected function setUp()
    {
        $this->cache = $this->createCachePool();

        ClockMock::register(__CLASS__);
        ClockMock::register(get_class($this->cache));
        ClockMock::withClockMock(true);
    }

    /**
     * @expectedException \Psr\Cache\InvalidArgumentException
     */
    public function testInvalidKey()
    {
        $pool = $this->createCachePool();

        $pool->getItem('test%string')->get();
    }

    public function testCleanupOnExpire()
    {
        $pool = $this->cache;

        $item = $pool->getItem('test_ttl_null');
        $item->set('data');
        $item->expiresAt(\DateTime::createFromFormat('U', time() + 5));
        $pool->save($item);
        $this->assertTrue($this->getFilesystem()->has('cache/test_ttl_null'));

        sleep(10);

        $item = $pool->getItem('test_ttl_null');
        $this->assertFalse($item->isHit());
        $this->assertFalse($this->getFilesystem()->has('cache/test_ttl_null'));
    }
}
