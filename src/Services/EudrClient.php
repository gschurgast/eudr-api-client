<?php

namespace src\Services;

use src\Enum\Environment;
use src\Enum\Mode;

class EudrClient
{
    protected string $username;
    protected string $password;
    protected Environment $environment = Environment::ACCEPTANCE;

    public function __construct(
        string $username,
        string $password,
        Environment $environment,
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->environment = $environment;
    }

    public function getClient(Mode $mode): BaseSoapService
    {
        $client = match ($mode) {
            Mode::ECHO => new EudrEchoClient(),
            Mode::SUBMISSION => new EudrSubmissionClient(),
            Mode::RETRIEVAL => new EudrRetrievalClient(),
        };

        $client->setEnvironment($this->environment);
        $client->setUsername($this->username);
        $client->setPassword($this->password);

        return $client;
    }
}
