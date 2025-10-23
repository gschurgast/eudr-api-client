<?php

declare(strict_types=1);

namespace src\Dto;

use JMS\Serializer\Annotation\Type;
use src\Serializer\JmsSerializationTrait;

final class GetDdsInfoResponse
{
    use JmsSerializationTrait;

    public ?string $identifier = null;

    public ?string $referenceNumber = null;

    public ?string $verificationNumber = null;

    public ?string $status = null;

    #[Type("DateTime<'Y-m-d\\TH:i:s.vP'>")]
    public ?\DateTime $date = null;

    public ?string $updatedBy = null;
}
