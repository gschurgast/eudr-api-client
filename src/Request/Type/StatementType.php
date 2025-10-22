<?php

namespace src\Request\Type;

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

    public function __construct()
    {
    }
}
