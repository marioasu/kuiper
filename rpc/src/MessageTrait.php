<?php

namespace kuiper\rpc;

use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

trait MessageTrait
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        if (!$this->stream) {
            $this->stream = stream_for('');
        }

        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     *
     * @return $this
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $attribute, $default = null)
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function withAttribute(string $attribute, $value)
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function withoutAttribute(string $attribute)
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }
}
