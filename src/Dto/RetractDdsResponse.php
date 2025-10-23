<?php

declare(strict_types=1);

namespace src\Dto;

use src\Serializer\JmsSerializationTrait;

final class RetractDdsResponse
{
    use JmsSerializationTrait;

    public ?string $status = null;
}
