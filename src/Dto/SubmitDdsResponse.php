<?php

declare(strict_types=1);

namespace src\Dto;

final class SubmitDdsResponse
{
    public string $ddsIdentifier = '';

    public static function fromSoap(mixed $soapResult): self
    {
        $self                = new self();
        $self->ddsIdentifier = $soapResult->ddsIdentifier;

        return $self;
    }
}
