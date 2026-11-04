<?php

namespace src\Enum;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum EnvironmentEnum: string
{
    case PRODUCTION = 'production';
    case ACCEPTANCE = 'acceptance';

    /**
     * Returns the base host for the selected environment.
     */
    public function baseHost(): string
    {
        return match ($this) {
            self::PRODUCTION => 'https://eudr.webcloud.ec.europa.eu',
            self::ACCEPTANCE => 'https://acceptance.eudr.webcloud.ec.europa.eu',
        };
    }

    /**
     * Build a full service endpoint URL from a relative path.
     * Example: /tracesnt/ws/EudrEchoService.
     */
    public function getUrl(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        return $this->baseHost() . $path;
    }

    /**
     * Default webServiceClientId per environment.
     * - acceptance -> eudr-test
     * - production -> eudr-repository.
     */
    public function getWebServiceClientId(): string
    {
        return match ($this) {
            self::ACCEPTANCE => 'eudr-test',
            self::PRODUCTION => 'eudr-repository',
        };
    }
}
