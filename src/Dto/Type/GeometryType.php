<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class GeometryType
{
    #[Type('string')]
    public string $type = 'Polygon';

    /** @var array<int, array<int, array<float>>> */
    #[Type('array<array<array<float>>>')]
    public array $coordinates = [];
}
