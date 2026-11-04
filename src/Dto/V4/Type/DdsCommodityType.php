<?php

namespace src\Dto\V4\Type;

use JMS\Serializer\Annotation\Type;
use src\Dto\Type\DescriptorsType;
use src\Dto\Type\ProducerType;
use src\Dto\Type\SpeciesInfoType;

/**
 * V4 accepts any HS code of 2 to 10 digits, not only the wood headings of WoodHeadingEnum.
 */
class DdsCommodityType
{
    public ?int $position = null;

    public DescriptorsType $descriptors;

    public string $hsHeading;

    /** @var SpeciesInfoType[]|null */
    #[Type('array<src\\Dto\\Type\\SpeciesInfoType>')]
    public ?array $speciesInfo = null;

    /** @var ProducerType[]|null */
    #[Type('array<src\\Dto\\Type\\ProducerType>')]
    public ?array $producers = null;
}
