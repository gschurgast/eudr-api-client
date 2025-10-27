<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use src\Enum\WoodHeadingEnum;

class CommodityType
{
    public ?int $position = null;

    public ?DescriptorsType $descriptors = null;

    #[SerializedName('hsHeading')]
    #[Type('string')]
    #[Accessor(getter: 'getHsHeadingValue', setter: 'setHsHeadingValue')]
    public ?WoodHeadingEnum $hsHeading = null;

    /** @var SpeciesInfoType[]|null */
    #[Type('array<src\\Dto\\Type\\SpeciesInfoType>')]
    public ?array $speciesInfo = null; // array of SpeciesInfoType

    /** @var ProducerType[]|null */
    #[Type('array<src\\Dto\\Type\\ProducerType>')]
    public ?array $producers = null;

    public function getHsHeadingValue(): ?string
    {
        return $this->hsHeading?->value;
    }

    public function setHsHeadingValue(?string $value): void
    {
        $this->hsHeading = $value !== null ? (WoodHeadingEnum::tryFrom($value) ?? WoodHeadingEnum::DENSIFIED_WOOD_4413) : null;
    }
}
