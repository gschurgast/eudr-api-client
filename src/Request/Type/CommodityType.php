<?php

namespace src\Request\Type;

class CommodityType
{
    public ?DescriptorsType $descriptors = null;
    public ?string $hsHeading = null;
    /**
     * @var SpeciesInfoType[]|null
     */
    public ?array $speciesInfo = null; // array of SpeciesInfoType
    /**
     * @var ProducerType[]|null
     */
    public ?array $producers = null; // array of ProducerType
}
