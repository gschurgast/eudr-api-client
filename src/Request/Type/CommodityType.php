<?php

namespace src\Request\Type;

use src\Enum\WoodHeadingEnum;

class CommodityType
{
    public ?DescriptorsType $descriptors = null;
    public ?WoodHeadingEnum $hsHeading = null;
    /**
     * @var SpeciesInfoType[]|null
     */
    public ?array $speciesInfo = null; // array of SpeciesInfoType
    /**
     * @var ProducerType[]|null
     */
    public ?array $producers = null; // array of ProducerType


    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $descriptors = null;
        if ($this->descriptors instanceof DescriptorsType) {
            $descriptors = [
                'descriptionOfGoods' => $this->descriptors->descriptionOfGoods,
            ];
            if ($this->descriptors->goodsMeasure instanceof GoodsMeasureType) {
                $descriptors['goodsMeasure'] = [
                    'netWeight' => $this->descriptors->goodsMeasure->netWeight ?? null,
                    'volume'    => $this->descriptors->goodsMeasure->volume ?? null,
                ];
            }
        }

        $speciesInfo = [];
        foreach (($this->speciesInfo ?? []) as $s) {
            $speciesInfo[] = [
                'scientificName' => $s->scientificName ?? null,
                'commonName'     => $s->commonName ?? null,
            ];
        }

        $producers = [];
        foreach (($this->producers ?? []) as $p) {
            $producers[] = [
                'country'         => $p->country ?? null,
                'name'            => $p->name ?? null,
                'geometryGeojson' => $p->geometryGeojson ?? null,
            ];
        }

        return [
            'descriptors' => $descriptors,
            'hsHeading'   => $this->hsHeading ? $this->hsHeading->value : null,
            'speciesInfo' => $speciesInfo,
            'producers'   => $producers,
        ];
    }
}
