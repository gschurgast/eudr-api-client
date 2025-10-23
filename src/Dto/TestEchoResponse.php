<?php

declare(strict_types=1);

namespace src\Dto;

use src\Serializer\JmsSerializationTrait;

final class TestEchoResponse
{
    use JmsSerializationTrait;

    public string $status = '';
}
