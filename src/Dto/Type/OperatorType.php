<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class OperatorType
{
    public ?EconomicOperatorReferenceNumberType $referenceNumber = null;

    public ?OperatorNameAndAddressType $operatorAddress = null;

    public ?string $email = null;

    public ?string $phone = null;

}
