# EUDR API SOAP Client (PHP)

This repository provides a minimal, typed PHP client for the European Union Deforestation Regulation (EUDR) SOAP services.
It exposes small service classes, request/response DTOs, and a functional PHPUnit test. Internally it uses JMS Serializer
for consistent array<->object transformations instead of ad‑hoc toArray/fromSoap code.

## What's included

- Services (src/Services):
    - EudrEchoClient
    - EudrSubmissionClient
    - EudrRetrievalClient
    - EudrClient facade to access the right service by ModeEnum
- Request/Response DTOs (src/Dto) with JMS Serializer attributes
    - Requests: SubmitDdsRequest, AmendDdsRequest, GetDdsInfoRequest, GetStatementByIdentifiersRequest, GetReferenceDdsRequest, etc.
    - Responses: TestEchoResponse, SubmitDdsResponse, AmendDdsResponse, RetractDdsResponse, GetDdsInfoResponse, GetStatementByIdentifiersResponse, GetReferenceDdsResponse
    - Typed sub-objects in src/Dto/Type (StatementType, CommodityType, OperatorType, StatusType, ...)
- A DTO factory for building request objects from associative arrays: src/Factory/DdsWsdlDtoFactory
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

## Usage examples

### Echo

```
$echoReq = new TestEchoRequest();
$echoReq->query = "My test query"
$testEchoResponse = $client->getClient(ModeEnum::ECHO)->testEcho($echoReq);
```

### Submit

#### Submit DDS Request

```
$submitReq = new SubmitDdsRequest();
$submitReq->operatorType = OperatorTypeEnum::OPERATOR;
$submitReq->statement = new StatementType();
...
$submitDdsResponse = $client->getClient(ModeEnum::SUBMISSION)->submitDds($submitReq);
```

#### Amend DDS Request

```
$amendReq = new AmendDdsRequest();
$amendReq->ddsIdentifier = {{<ddsIdentifier>}};
$amendReq->statement = new StatementType();
...
$amendDdsResponse = $client->getClient(ModeEnum::SUBMISSION)->amendDds($amendReq);
```

#### Retract DDS Request

```
$retractReq = new AmendDdsRequest();
$retractReq->ddsIdentifier = {{<ddsIdentifier>}};
$retractReq->reason = "Reason of retractation";
$retractDdsResponse = $client->getClient(ModeEnum::SUBMISSION)->retractDds($retractReq);
```

### Retrieval

#### Get DDS Info Request

```
$getDDSInfoReq = new GetDdsInfoRequest
$getDDSInfoReq->identifier = {{<ddsIdentifier>}};
$getDDSInfoResponse = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfo($getDDSInfoReq);
```

#### Get DDS Info by Internal Reference Number

```
$getDDSInfoByInternalRefReq = new GetDdsInfoByInternalReferenceNumberRequest
$getDDSInfoByInternalRefReq->identifier = {{<my_reference_number>}};
$getDDSInfoByInternalRefResponse = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfoByInternalReferenceNumber($getDDSInfoReq);
```

##### Get Statement by DDS Identifier

```
$getStatementByIdentifierReq = new GetStatementByIdentifiersRequest
$getStatementByIdentifierReq->referenceNumber = {{<ddsReferenceNumber>}};
$getStatementByIdentifierReq->verificationNumber = {{<ddsVerificationNumber>}};
$getStatementByIdentifierResponse = $client->getClient(ModeEnum::RETRIEVAL)->getStatementByIdentifiers($getDDSInfoReq);
```

#### Get Reference DDS (No test has been performed on this method)

```
$getReferenceDDSReq = new GetReferenceDdsRequest
$getReferenceDDSReq->referenceNumber = {{<ddsReferenceNumber>}};
$getReferenceDDSReq->referenceDdsVerificationNumber = {{<ddsVerificationNumber>}};
$getReferenceDDSResponse = $client->getClient(ModeEnum::RETRIEVAL)->getReferencedDds($getDDSInfoReq);
```

## Functional tests

- tests/Functional/DemoTest.php builds DTOs from fixtures (tests/fixtures/payloads.yaml) and can perform real calls when credentials are valid.
- Use composer run test:functional to execute the suite.

## Notes

- EudrEchoClient operations must not be used in production environments per EUDR rules.
- Service methods return dedicated Response DTOs mapping SOAP responses to typed PHP objects.

## License

MIT
