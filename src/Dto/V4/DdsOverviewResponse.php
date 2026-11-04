<?php

namespace src\Dto\V4;

use JMS\Serializer\Annotation\Type;
use src\Dto\V4\Type\OverviewType;
use src\Serializer\JmsSerializationTrait;

/**
 * Response of getDds and getDdsByInternalReference.
 */
final class DdsOverviewResponse
{
    use JmsSerializationTrait;

    /** @var OverviewType[] */
    #[Type('array<src\\Dto\\V4\\Type\\OverviewType>')]
    public array $ddsOverviewList = [];
}
