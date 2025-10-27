<?php

declare(strict_types=1);

namespace src\Serializer;

use JMS\Serializer\Serializer;
use Webmozart\Assert\Assert;

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
        $json = json_encode($soapResult);
        Assert::string($json);

        // 🧠 Désérialiser le JSON vers le DTO
        /** @var self $obj */
        $obj = self::serializer()->deserialize($json, self::class, 'json');

        return $obj;
    }
}
