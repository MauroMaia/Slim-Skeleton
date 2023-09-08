<?php

declare(strict_types=1);

namespace App\Application;

use JsonSerializable;

readonly class ActionPayload implements JsonSerializable
{

    public function __construct(
        public int          $statusCode = 200,
        public mixed        $data = null,
        public ?ActionError $error = null
    )
    {
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        $payload = [];

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        return $payload;
    }
}
