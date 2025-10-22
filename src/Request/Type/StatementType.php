<?php

namespace src\Request\Type;


/**
 * @phpstan-type StatementArray array{
 *     internalReferenceNumber?: string,
 *     activityType: string,
 *     countryOfActivity: string,
 *     borderCrossCountry: string,
 *     comment: string,
 *     geoLocationConfidential?: bool,
 *     commodities?: list<array<string, mixed>>,
 *     operator?: array<string, mixed>
 * }
 */
class StatementType
{
    public string $internalReferenceNumber;
    public string $activityType;
    public ?string $comment = null;
    public ?string $countryOfActivity = null;
    public ?string $borderCrossCountry = null;
    /**
     * @var CommodityType[]|null
     */
    public ?array $commodities = null; // array of CommodityType
    public ?OperatorType $operator = null;
    public bool $geoLocationConfidential = false;

    /**
     * @return StatementArray
     */
    public function toArray(): array
    {
        $commodities = [];
        foreach (($this->commodities ?? []) as $commodity) {
            $commodities[] = $commodity->toArray();
        }

        $result = [
            'internalReferenceNumber' => $this->internalReferenceNumber,
            'activityType'            => $this->activityType,
            'comment'                 => (string)($this->comment ?? ''),
            'countryOfActivity'       => (string)($this->countryOfActivity ?? ''),
            'borderCrossCountry'      => (string)($this->borderCrossCountry ?? ''),
            'commodities'             => $commodities,
            'geoLocationConfidential' => (bool)$this->geoLocationConfidential,
        ];

        if ($this->operator instanceof OperatorType) {
            $op = [
                'email' => $this->operator->email,
                'phone' => $this->operator->phone,
            ];
            if ($this->operator->nameAndAddress) {
                $op['nameAndAddress'] = [
                    'name'    => $this->operator->nameAndAddress->name ?? null,
                    'country' => $this->operator->nameAndAddress->country ?? null,
                    'address' => $this->operator->nameAndAddress->address ?? null,
                ];
            }
            $result['operator'] = $op;
        }

        return $result;
    }
}
