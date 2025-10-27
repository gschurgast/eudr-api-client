<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class GeometryGeojsonType
{
    #[Type('string')]
    public string $type = 'FeatureCollection';

    /** @var array<int, FeatureType> */
    #[Type('array<src\\Dto\\Type\\FeatureType>')]
    public array $features = [];

    public function validate(): void
    {
        foreach ($this->features as $feature) {
            $feature->geometry->validate();
        }
    }
}
