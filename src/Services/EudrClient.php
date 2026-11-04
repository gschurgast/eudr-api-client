<?php

namespace src\Services;

use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;

class EudrClient
{
    protected string $username;

    protected string $password;

    protected EnvironmentEnum $environment = EnvironmentEnum::ACCEPTANCE;

    /** @var array<string, BaseSoapService> */
    private array $clients = [];

    public function __construct(
        string $username,
        string $password,
        EnvironmentEnum $environment,
        protected ?string $operatorAccessIdentifier = null,
    ) {
        $this->username    = $username;
        $this->password    = $password;
        $this->environment = $environment;
    }

    public function getClient(ModeEnum $mode): BaseSoapService
    {
        if (!isset($this->clients[$mode->value])) {
            $client = match ($mode) {
                ModeEnum::ECHO                       => new EudrEchoClient(),
                ModeEnum::DUE_DILIGENCE_STATEMENT_V4 => new EudrDueDiligenceStatementClient(),
            };

            $client->setEnvironment($this->environment);
            $client->setUsername($this->username);
            $client->setPassword($this->password);
            if ($client instanceof EudrDueDiligenceStatementClient) {
                $client->setOperatorAccessIdentifier($this->operatorAccessIdentifier);
            }

            $this->clients[$mode->value] = $client;
        }

        return $this->clients[$mode->value];
    }
}
