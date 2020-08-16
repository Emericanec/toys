<?php

declare(strict_types=1);

namespace app\requests;

abstract class AbstractRequest implements RequestInterface
{
    protected ?string $error = null;

    public function getError(): ?string
    {
        return $this->error;
    }
}
