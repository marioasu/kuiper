<?php

namespace kuiper\cache;

use kuiper\cache\driver\Memory;

class MemoryDriverTest extends BaseDriverTestCase
{
    protected function createCachePool()
    {
        return new Pool(new Memory());
    }
}
