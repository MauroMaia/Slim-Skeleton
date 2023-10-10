<?php

declare(strict_types=1);

namespace App\Domain\Role;

use JsonSerializable;

readonly class Role implements JsonSerializable
{
    public function __construct(
        public ?int       $id,
        public string     $name,
        public array $permissions,
        public ?\DateTime $createdAt = new \DateTime('now'),
        public ?\DateTime $updatedAt = new \DateTime('now')) { }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions,
            'createdAt' => $this->createdAt->format(\DateTime::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTime::ATOM),
        ];
    }
}
