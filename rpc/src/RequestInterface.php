<?php

namespace kuiper\rpc;

interface RequestInterface extends MessageInterface
{
    /**
     * Gets the parsed parameters of the body.
     *
     * @return string
     */
    public function getMethod();

    /**
     * @param $method
     *
     * @return self
     */
    public function withMethod($method);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param array $parameters
     *
     * @return self
     */
    public function withParameters(array $parameters);
}
