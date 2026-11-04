<?php

declare(strict_types=1);

namespace src\Dto\V4;

use JMS\Serializer\Annotation\Type;
use src\Serializer\JmsSerializationTrait;

class GetDdsRequest
{
    use JmsSerializationTrait;

    /** @var string[] At most 100 UUIDs per call. */
    #[Type('array<string>')]
    public array $uuidList = [];
}
