<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

class StatusType
{
    public string $status;

    #[Type("DateTime<'Y-m-d\\TH:i:s.v'>")]
    public \DateTime $date;
}
