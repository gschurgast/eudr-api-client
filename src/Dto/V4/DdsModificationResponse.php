<?php

declare(strict_types=1);

namespace src\Dto\V4;

use src\Serializer\JmsSerializationTrait;

/**
 * Response of amendDds and withdrawDds.
 */
final class DdsModificationResponse
{
    use JmsSerializationTrait;

    public string $uuid = '';

    public ?string $status = null;
}
