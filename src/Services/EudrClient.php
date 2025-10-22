<?php

namespace src\Services;

use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;

class EudrClient
{
    protected string $username;
    protected string $password;
    protected EnvironmentEnum $environment = EnvironmentEnum::ACCEPTANCE;

    public function __construct(
        string $username,
        string $password,
        EnvironmentEnum $environment,
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->environment = $environment;
    }

    public function getClient(ModeEnum $mode): BaseSoapService
    {
        $client = match ($mode) {
            ModeEnum::ECHO => new EudrEchoClient(),
            ModeEnum::SUBMISSION => new EudrSubmissionClient(),
            ModeEnum::RETRIEVAL => new EudrRetrievalClient(),
        };

        $client->setEnvironment($this->environment);
        $client->setUsername($this->username);
        $client->setPassword($this->password);

        return $client;
    }
}
