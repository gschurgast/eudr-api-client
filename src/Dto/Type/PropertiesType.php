<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class PropertiesType
{
    #[Type('string')]
    public string $gjid;

    #[Type('int')]
    public int $godina;

    #[Type('float')]
    public float $povrsina;

    #[Type('string')]
    public string $oznaka;
}
