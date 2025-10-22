<?php

namespace src\Request\Type;

class GoodsMeasureType
{
    public ?float $netWeight = null;
    public ?float $volume = null;
    public ?float $supplementaryUnit = null;
    public ?string $supplementaryUnitQualifier = null;
    public ?float $percentageEstimationOrDeviation = null;
}
