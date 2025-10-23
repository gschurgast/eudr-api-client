<?php

declare(strict_types=1);

namespace src\Dto;

use src\Serializer\JmsSerializationTrait;

final class GetReferenceDdsResponse
{
    use JmsSerializationTrait;

    public ?string $referenceNumber = null;

    public ?string $status = null;
}
