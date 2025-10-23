<?php

declare(strict_types=1);

namespace src\Dto;

use src\Dto\Type\CommodityType;
use src\Dto\Type\DescriptorsType;
use src\Dto\Type\GoodsMeasureType;
use src\Dto\Type\OperatorResponseType;
use src\Dto\Type\ProducerType;
use src\Dto\Type\SpeciesInfoType;
use src\Dto\Type\StatusType;
use src\Enum\WoodHeadingEnum;

final class GetStatementByIdentifiersResponse
{
    public ?string $referenceNumber = null;

    public ?string $activityType = null;

    public ?StatusType $status = null;

    public ?OperatorResponseType $operator = null;

    /** @var CommodityType[] */
    public ?array $commodities = [];

    public static function fromSoap(mixed $soapResult): self
    {
        $self                    = new self();
        $data                    = $soapResult->statement;
        $self->referenceNumber   = $data->referenceNumber;
        $self->activityType      = $data->activityType;
        $self->status            = new StatusType();
        $self->status->status    = $data->status->status;
        $self->status->date      = new \DateTime($data->status->date);
        $self->operator          = new OperatorResponseType();
        $self->operator->name    = $data->operator->name;
        $self->operator->country = $data->operator->country;
        $self->commodities       = [];
        foreach ($data->commodities as $dataCommodity) {
            $commodity                                       = new CommodityType();
            $commodity->position                             = $dataCommodity->position;
            $commodity->descriptors                          = new DescriptorsType();
            $commodity->descriptors->descriptionOfGoods      = $dataCommodity->descriptors->descriptionOfGoods;
            $commodity->descriptors->goodsMeasure            = new GoodsMeasureType();
            $commodity->descriptors->goodsMeasure->netWeight = (float) $dataCommodity->descriptors->goodsMeasure->netWeight;
            $commodity->hsHeading                            = WoodHeadingEnum::tryFrom($dataCommodity->hsHeading);
            $commodity->speciesInfo                          = [];
            foreach ($dataCommodity->speciesInfo as $dataSpeciesInfo) {
                $speciesInfo                 = new SpeciesInfoType();
                $speciesInfo->scientificName = $dataSpeciesInfo->scientificName;
                $speciesInfo->commonName     = $dataSpeciesInfo->commonName;
                $commodity->speciesInfo[]    = $speciesInfo;
            }
            $commodity->producers = [];
            foreach ($dataCommodity->producers as $dataProducer) {
                $producer                  = new ProducerType();
                $producer->country         = $dataProducer->country;
                $producer->geometryGeojson = $dataProducer->geometryGeojson;
                $commodity->producers[]    = $producer;
            }

            $self->commodities[] = $commodity;
        }

        return $self;
    }
}
