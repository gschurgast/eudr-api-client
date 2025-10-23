<?php

declare(strict_types=1);

namespace src\Dto;

final class GetDdsInfoResponse
{
    public ?string $identifier = null;

    public ?string $referenceNumber = null;

    public ?string $verificationNumber = null;

    public ?string $status = null;

    public ?\DateTime $date = null;

    public ?string $updatedBy = null;

    public static function fromSoap(mixed $soapResult): self
    {
        $data = $soapResult->statementInfo[0] ?? $soapResult;

        $self                     = new self();
        $self->identifier         = $data->identifier ?? null;
        $self->referenceNumber    = $data->referenceNumber ?? null;
        $self->verificationNumber = $data->verificationNumber ?? null;
        $self->status             = $data->status;
        $self->date               = $data->date ? new \DateTime($data->date) : null;
        $self->updatedBy          = $data->updatedBy ?? null;

        return $self;
    }
}
