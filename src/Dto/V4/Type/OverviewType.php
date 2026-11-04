<?php

namespace src\Dto\V4\Type;

final class OverviewType
{
    public string $uuid = '';

    public ?string $internalReferenceNumber = null;

    public ?string $referenceNumber = null;

    public ?string $referenceNumberInfo = null;

    public ?string $verificationNumber = null;

    public ?string $status = null;

    public ?string $rejectionReason = null;

    public ?CommunicationToOperatorType $communicationToOperator = null;

    public ?string $date = null;

    public ?string $updatedBy = null;

    public ?int $version = null;
}
