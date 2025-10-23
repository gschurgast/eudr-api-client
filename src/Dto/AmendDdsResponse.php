<?php

declare(strict_types=1);

namespace src\Dto;

final class AmendDdsResponse
{
    public ?string $status = null;

    public static function fromSoap(mixed $soapResult): self
    {
        $self         = new self();
        $self->status = $soapResult->status;

        return $self;
    }
}
