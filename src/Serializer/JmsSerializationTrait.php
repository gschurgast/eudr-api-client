<?php

declare(strict_types=1);

namespace src\Serializer;

use JMS\Serializer\Serializer;

trait JmsSerializationTrait
{
    protected static function serializer(): Serializer
    {
        $s = SerializerFactory::get();

        return $s;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::serializer()->toArray($this);
    }

    /**
     * Build DTO from SOAP result using JMS Serializer array transformation.
     */
    public static function fromSoap(mixed $soapResult): self
    {
        $data = self::serializer()->toArray($soapResult);

        /** @var self $obj */
        $obj = self::serializer()->fromArray($data, self::class);

        return $obj;
    }
}
