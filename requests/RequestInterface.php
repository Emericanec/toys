<?php

declare(strict_types=1);

namespace app\requests;

interface RequestInterface
{
    public function validate(): bool;

    public function getError(): ?string;
}
