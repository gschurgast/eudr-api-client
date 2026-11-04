<?php

namespace src\Dto\V4;

use src\Serializer\JmsSerializationTrait;

class WithdrawDdsRequest
{
    use JmsSerializationTrait;

    public string $uuid;
}
