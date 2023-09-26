<?php

declare(strict_types=1);

namespace App\Domain\Role;

use JsonSerializable;

readonly class Role implements JsonSerializable
{
    public function __construct(
        public ?int       $id,
        public string     $name,
        public ?\DateTime $createdAt,
        public ?\DateTime $updatedAt) { }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->name,
            'createdAt' => $this->createdAt->format(\DateTime::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTime::ATOM),
        ];
    }
}
