<?php

namespace src\Dto;

use src\Serializer\JmsSerializationTrait;

final class TestEchoResponse
{
    use JmsSerializationTrait;

    public string $status = '';
}
