<?php

namespace src\Dto\V4;

use src\Serializer\JmsSerializationTrait;

class GetDdsByInternalReferenceRequest
{
    use JmsSerializationTrait;

    public string $internalReference;
}
