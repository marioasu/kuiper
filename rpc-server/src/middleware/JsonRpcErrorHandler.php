<?php

namespace kuiper\rpc\server\middleware;

use kuiper\helper\Arrays;
use kuiper\rpc\MiddlewareInterface;
use kuiper\rpc\RequestInterface;
use kuiper\rpc\ResponseInterface;

class JsonRpcErrorHandler implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            return $this->handle($e, $request, $response);
        } catch (\Error $e) {
            return $this->handle($e, $request, $response);
        }
    }

    public function handle($exception, RequestInterface $request, ResponseInterface $response)
    {
        if ($exception instanceof \Serializable) {
            $data = $exception;
        } else {
            $data = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ];
        }
        $payload = $request->getAttribute('body');
        $response->getBody()->write(json_encode([
            'id' => Arrays::fetch($payload, 'id'),
            'jsonrpc' => Arrays::fetch($payload, 'version', '1.0'),
            'error' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'data' => base64_encode(serialize($data)),
            ],
        ]));

        return $response;
    }
}
