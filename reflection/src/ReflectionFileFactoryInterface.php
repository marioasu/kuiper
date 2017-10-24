<?php

namespace kuiper\reflection;

interface ReflectionFileFactoryInterface
{
    /**
     * Creates instance.
     *
     * @return self
     */
    public static function createInstance();

    /**
     * Creates ReflectionFileInterface instance.
     *
     * @param string $file
     *
     * @return ReflectionFileInterface
     */
    public function create($file);

    /**
     * Clears cached instance.
     *
     * @param string $file
     *
     * @return bool
     */
    public function clearCache($file = null);
}
