<?php

namespace Bookstore\CoreApi\Model\Data;

trait DataObjectTrait
{
    private array $data = [];

    protected function getValue(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    protected function setValue(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }
}
