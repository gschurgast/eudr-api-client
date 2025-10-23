<?php

namespace src\Dto\Type;

class ProducerType
{
    public ?string $country = null;

    public ?string $name = null;

    public ?string $geometryGeojson = null; // Base64 encoded string if used
}
