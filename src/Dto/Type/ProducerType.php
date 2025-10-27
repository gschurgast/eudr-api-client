<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class ProducerType
{
    public ?string $country = null;

    public ?string $name = null;

    /**
     * GeoJSON data. May be provided as a DTO, an associative array (fixtures), or a JSON string (SOAP).
     *
     * @var GeometryGeojsonType|array<string, mixed>|string|null
     */
    #[Type('geojson')]
    public GeometryGeojsonType|array|string|null $geometryGeojson = null;
}
