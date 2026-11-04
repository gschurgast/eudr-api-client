# EUDR API SOAP Client (PHP)

This repository provides a minimal, typed PHP client for the European Union Deforestation Regulation (EUDR) SOAP services.
It exposes small service classes, request/response DTOs, and a functional PHPUnit test. Internally it uses JMS Serializer
for consistent array<->object transformations instead of ad‑hoc toArray/fromSoap code.

## What's included

- Services (src/Services):
    - EudrEchoClient
    - EudrDueDiligenceStatementClient (EUDRDueDiligenceStatementServiceV4)
    - EudrClient facade to access the right service by ModeEnum
- Request/Response DTOs with JMS Serializer attributes
    - Echo: TestEchoRequest, TestEchoResponse (src/Dto)
    - V4 requests and responses in src/Dto/V4 (SubmitDdsRequest, AmendDdsRequest, WithdrawDdsRequest, GetDdsRequest, GetDdsByInternalReferenceRequest, ...)
    - Shared sub-objects in src/Dto/Type (ProducerType, GeometryGeojsonType, SpeciesInfoType, DescriptorsType, ...)
- Functional tests using PHPUnit: tests/Functional/DemoTest.php

## Requirements

- PHP >= 8.1
- ext-soap, ext-dom
- Composer

## Install

- make init

## Running tests or acceptance tests

- Edit your .env file to provide your credentials.

```
EUDR_USERNAME=your-username
EUDR_PASSWORD=your-password
```
- make test

## Create an EUDR client

- Choose the environment via src\Enum\EnvironmentEnum (ACCEPTANCE or PRODUCTION).
- Provide your credentials via environment variables (recommended for local dev put them in a .env file at the project root):


- Instantiate the high-level client and pass credentials read from env:

```
$client = new EudrClient(getenv('EUDR_USERNAME'), getenv('EUDR_PASSWORD'), EnvironmentEnum::ACCEPTANCE);
```  

- If your API user is linked to more than one operator, pass the operator access identifier as the fourth argument. The client then sends the `BodyIdentity` SOAP header.

## Usage examples

### Echo

```
$echoReq = new TestEchoRequest();
$echoReq->query = "My test query";
$testEchoResponse = $client->getClient(ModeEnum::ECHO)->testEcho($echoReq);
```

### Due Diligence Statement V4

The V2 Submission and Retrieval services are discontinued on acceptance and are no longer supported by this client. Use `ModeEnum::DUE_DILIGENCE_STATEMENT_V4`.
Only the `OPERATOR` role is supported. `getDdsByIdentifiers` is not exposed: the V4 schema states that it always returns `NotFoundException`.

```
$submitReq = new \src\Dto\V4\SubmitDdsRequest();
$submitReq->statement = new \src\Dto\V4\Type\DueDiligenceStatementType();
...
$uuid = $client->getClient(ModeEnum::DUE_DILIGENCE_STATEMENT_V4)->submitDds($submitReq)->uuid;

$getReq = new \src\Dto\V4\GetDdsRequest();
$getReq->uuidList = [$uuid];
$overview = $client->getClient(ModeEnum::DUE_DILIGENCE_STATEMENT_V4)->getDds($getReq)->ddsOverviewList[0];
// $overview->status, $overview->referenceNumber, $overview->verificationNumber

$withdrawReq = new \src\Dto\V4\WithdrawDdsRequest();
$withdrawReq->uuid = $uuid;
$client->getClient(ModeEnum::DUE_DILIGENCE_STATEMENT_V4)->withdrawDds($withdrawReq);
```

## Functional tests

- tests/Functional/DemoTest.php builds DTOs from fixtures (tests/fixtures/payloads.yaml) and can perform real calls when credentials are valid.
- tests/Functional/DueDiligenceStatementV4RequestTest.php validates each V4 request body against the official schema (tests/fixtures/xsd/v4). It needs network access to the acceptance WSDL, but no credentials.
- tests/Functional/WsSecurityHeaderTest.php checks that each call sends a new WS-Security nonce. It needs no credentials.
- Use composer run test:functional to execute the suite.

## Notes

- EudrEchoClient operations must not be used in production environments per EUDR rules.
- Each call sends a new WS-Security header (nonce, timestamp). The SoapClient and its WSDL stay cached per service.
- Service methods return dedicated Response DTOs mapping SOAP responses to typed PHP objects.

## License

MIT
