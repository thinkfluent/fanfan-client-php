<?php

declare(strict_types=1);

namespace FanFan\Client\Message;

trait HasPayloadTrait
{
    protected ?array $payload = null;

    public function payload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function hasPayload(): bool
    {
        return !empty($this->payload);
    }
}
