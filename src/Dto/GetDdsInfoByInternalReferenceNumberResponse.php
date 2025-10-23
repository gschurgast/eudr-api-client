<?php

declare(strict_types=1);

namespace src\Dto;

final class GetDdsInfoByInternalReferenceNumberResponse
{
    /**
     * @var GetDdsInfoResponse[]
     */
    public ?array $statementInfo = [];

    public static function fromSoap(mixed $soapResult): self
    {
        $self = new self();

        foreach ($soapResult as $v) {
            foreach ($v as $raw) {
                $self->statementInfo[] = GetDdsInfoResponse::fromSoap($raw);
            }
        }

        return $self;
    }
}
