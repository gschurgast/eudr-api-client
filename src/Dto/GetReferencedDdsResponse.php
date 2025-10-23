<?php

declare(strict_types=1);

namespace src\Dto;

final class GetReferencedDdsResponse
{
    public ?string $referenceNumber = null;

    public ?string $status = null;

    public static function fromSoap(mixed $soapResult): self
    {
        $self                  = new self();
        $self->referenceNumber = self::asString(self::findValue($soapResult, 'referenceNumber'));
        $self->status          = self::asString(self::findValue($soapResult, 'status'));

        return $self;
    }

    private static function asString(mixed $v): ?string
    {
        return \is_scalar($v) ? (string) $v : null;
    }

    private static function findValue(mixed $data, string $key): mixed
    {
        if (\is_array($data)) {
            if (\array_key_exists($key, $data)) {
                return $data[$key];
            }
            foreach ($data as $v) {
                $found = self::findValue($v, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        } elseif (\is_object($data)) {
            if (property_exists($data, $key)) {
                return $data->$key;
            }
            foreach (get_object_vars($data) as $v) {
                $found = self::findValue($v, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
