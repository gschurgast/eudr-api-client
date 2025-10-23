<?php

namespace src\Serializer;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

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

            self::$serializer = $builder->build();
        }

        return self::$serializer;
    }
}
