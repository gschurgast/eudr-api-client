<?php

namespace src\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use src\Dto\Type\GeometryGeojsonType;
use src\Serializer\SerializerFactory;
use Webmozart\Assert\Assert;

class GeoJsonHandler implements SubscribingHandlerInterface
{
    /**
     * @return array<int, array{
     *     direction: int,
     *     format: string,
     *     type: string,
     *     method: string
     * }>
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'geojson',
                'method'    => 'serializeGeoJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'geojson',
                'method'    => 'deserializeGeoJson',
            ],
        ];
    }

    /**
     * @param GeometryGeojsonType|array<string, mixed>|string|null $data
     * @param array<string, mixed>                                 $type
     */
    public function serializeGeoJson(JsonSerializationVisitor $visitor, mixed $data, array $type): ?string
    {
        $serializer = SerializerFactory::get();

        if ($data instanceof GeometryGeojsonType) {
            $data = $serializer->toArray($data);
        }

        if (!\is_array($data)) {
            return $data;
        }
        $res = json_encode($data, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        Assert::string($res);

        return $res;
    }

    /**
     * @param string|array<string, mixed>|null $data
     * @param array<string, mixed>             $type
     */
    public function deserializeGeoJson(JsonDeserializationVisitor $visitor, mixed $data, array $type): ?GeometryGeojsonType
    {
        // Récupérer le serializer interne
        $serializer = SerializerFactory::get();

        if (\is_string($data)) {
            $data = json_decode($data, true);
        }

        if (\is_array($data)) {
            return $serializer->fromArray($data, GeometryGeojsonType::class);
        }

        return null;
    }
}
