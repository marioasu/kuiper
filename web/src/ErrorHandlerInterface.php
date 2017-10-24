<?php

namespace kuiper\web;

interface ErrorHandlerInterface extends RequestAwareInterface, ResponseAwareInterface
{
    /**
     * Handles the exception.
     *
     * @param \Error $exception
     */
    public function handle($exception);
}
