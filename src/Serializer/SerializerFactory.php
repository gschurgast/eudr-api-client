<?php

namespace src\Serializer;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use src\Serializer\Handler\GeoJsonHandler;

final class SerializerFactory
{
    private static ?Serializer $serializer = null;

    public static function get(): Serializer
    {
        if (self::$serializer === null) {
            $builder = SerializerBuilder::create();

            // ⚙️ Force les clés à rester en camelCase
            $builder->setPropertyNamingStrategy(
                new SerializedNameAnnotationStrategy(
                    new IdenticalPropertyNamingStrategy()
                )
            );

            $builder->configureHandlers(function ($registry) {
                $registry->registerSubscribingHandler(new GeoJsonHandler());
            });

            self::$serializer = $builder->build();
        }

        return self::$serializer;
    }
}
