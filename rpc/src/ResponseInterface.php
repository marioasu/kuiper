<?php

namespace kuiper\rpc;

interface ResponseInterface extends MessageInterface
{
    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @param mixed $result
     *
     * @return static
     */
    public function withResult($result);
}
