<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class FeatureType
{
    #[Type('string')]
    public string $type = 'Feature';

    #[Type('src\\Dto\\Type\\GeometryType')]
    public GeometryType $geometry;

    #[Type('src\\Dto\\Type\\PropertiesType')]
    public PropertiesType $properties;
}
