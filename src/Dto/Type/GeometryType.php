<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use src\Enum\GeometryTypeEnum;

class GeometryType
{
    #[SerializedName('type')]
    #[Type('string')]
    #[Accessor(getter: 'getGeometryTypeValue', setter: 'setGeometryTypeValue')]
    public GeometryTypeEnum $type = GeometryTypeEnum::POLYGON;

    /** @var array<int, mixed> */
    #[Type('array')]
    public array $coordinates = [];

    public function getGeometryTypeValue(): string
    {
        return $this->type->value;
    }

    public function setGeometryTypeValue(string $value): void
    {
        $this->type = GeometryTypeEnum::tryFrom($value) ?? GeometryTypeEnum::POLYGON;
    }

    /**
     * 🧩 Vérifie la structure des coordonnées selon le type GeoJSON
     */
    public function validate(): void
    {
        switch ($this->type) {
            case GeometryTypeEnum::POINT:
                if (!$this->isValidPoint($this->coordinates)) {
                    throw new \InvalidArgumentException('Invalid coordinates for Point: expected [lon, lat]');
                }
                break;

            case GeometryTypeEnum::MULTIPOINT:
            case GeometryTypeEnum::LINESTRING:
                $this->validateArrayOfPoints($this->coordinates);
                break;

            case GeometryTypeEnum::MULTILINESTRING:
            case GeometryTypeEnum::POLYGON:
                $this->validateArrayOfArrayOfPoints($this->coordinates);
                break;

            case GeometryTypeEnum::MULTIPOLYGON:
                $this->validateArrayOfArrayOfArrayOfPoints($this->coordinates);
                break;
        }
    }

    /**
     * @param array<float> $coords Tableau de points [lon, lat]
     */
    private function isValidPoint(mixed $coords): bool
    {
        return \count($coords) === 2;
    }

    /**
     * @param array<array<float>> $coords Tableau de points [lon, lat]
     */
    private function validateArrayOfPoints(array $coords): void
    {
        foreach ($coords as $point) {
            if (!$this->isValidPoint($point)) {
                throw new \InvalidArgumentException('Invalid coordinates: expected array of [lon, lat]');
            }
        }
    }

    /**
     * @param array<array<array<float>>> $coords Tableau de tableaux points [[lon, lat], [lon, lat], ...]
     */
    private function validateArrayOfArrayOfPoints(array $coords): void
    {
        foreach ($coords as $ring) {
            $this->validateArrayOfPoints($ring);

            // Vérifie la fermeture de l’anneau (Polygon)
            if ($this->type === GeometryTypeEnum::POLYGON || $this->type === GeometryTypeEnum::MULTIPOLYGON) {
                $first = $ring[0] ?? null;
                $last  = $ring[\count($ring) - 1] ?? null;

                if ($first !== $last) {
                    throw new \InvalidArgumentException(
                        'Invalid Polygon: first and last coordinates must be identical (ring must be closed).'
                    );
                }
            }
        }
    }

    /**
     * @param array<array<array<array<float>>>> $coords Tableau de tableaux points [[[lon, lat], [lon, lat]],[[lon, lat], [lon, lat]]]
     */
    private function validateArrayOfArrayOfArrayOfPoints(array $coords): void
    {
        foreach ($coords as $polygon) {
            $this->validateArrayOfArrayOfPoints($polygon);
        }
    }
}
