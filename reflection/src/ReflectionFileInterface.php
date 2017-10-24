<?php

namespace kuiper\reflection;

interface ReflectionFileInterface
{
    /**
     * Gets the file name.
     *
     * @return string
     */
    public function getFile();

    /**
     * Gets all namespaces defined in the file.
     *
     * @return string[]
     *
     * @throws exception\SyntaxErrorException
     */
    public function getNamespaces();

    /**
     * Gets all classes defined in the file.
     *
     * @return string[]
     *
     * @throws exception\SyntaxErrorException
     */
    public function getClasses();

    /**
     * Gets all imported classes in the namespace
     * return array key is alias, value is the Full Qualified Class Name.
     *
     * @param string $namespace
     *
     * @return string[]
     */
    public function getImportedClasses($namespace);

    /**
     * Gets all imported functions in the namespace
     * return array key is alias, value is the Full Qualified Function Name.
     *
     * @param string $namespace
     *
     * @return string[]
     */
    public function getImportedFunctions($namespace);

    /**
     * Gets all imported constants in the namespace
     * return array key is alias, value is the Full Qualified Constant Name.
     *
     * @param string $namespace
     *
     * @return string[]
     */
    public function getImportedConstants($namespace);
}
