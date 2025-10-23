<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;
use src\Enum\WoodHeadingEnum;

class CommodityType
{
    public ?int $position = null;

    public ?DescriptorsType $descriptors = null;

    #[Exclude]
    public ?WoodHeadingEnum $hsHeading = null;

    /** @var SpeciesInfoType[]|null */
    #[Type('array<src\\Dto\\Type\\SpeciesInfoType>')]
    public ?array $speciesInfo = null; // array of SpeciesInfoType

    /** @var ProducerType[]|null */
    #[Type('array<src\\Dto\\Type\\ProducerType>')]
    public ?array $producers = null; // array of ProducerType

    #[VirtualProperty]
    #[SerializedName('hsHeading')]
    #[Type('string')]
    public function getHsHeadingValue(): ?string
    {
        return $this->hsHeading?->value;
    }
}
