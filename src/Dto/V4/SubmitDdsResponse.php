<?php

declare(strict_types=1);

namespace src\Dto\V4;

use src\Serializer\JmsSerializationTrait;

final class SubmitDdsResponse
{
    use JmsSerializationTrait;

    public string $uuid = '';
}
