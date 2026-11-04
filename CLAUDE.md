# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP SOAP client library for the European Union Deforestation Regulation (EUDR) API. It provides typed request/response DTOs and service clients for interacting with the EU's EUDR web services.

## Common Commands

```bash
# Initialize project (builds Docker and starts containers)
make init

# Run functional tests (requires .env with credentials)
make test

# Run PHPStan static analysis (level 8)
make phpstan

# Run PHP-CS-Fixer to format code
make phpcsfixer

# Direct composer scripts (via Docker)
composer run test:functional
composer run dev:phpstan
composer run dev:phpcsfixer
```

**Note:** All commands run through Docker. The project requires a `.env` file with `EUDR_USERNAME` and `EUDR_PASSWORD` for functional tests.

## Architecture

### Service Layer (src/Services/)
- **EudrClient** - Main facade that creates and caches the appropriate service client based on `ModeEnum`
- **BaseSoapService** - Abstract base handling WS-Security authentication (PasswordDigest with nonce/timestamp) and SoapClient construction
- **EudrEchoClient** - Test/echo operations (ACCEPTANCE environment only)
- **EudrDueDiligenceStatementClient** - V4 service: submit, amend, withdraw DDS, get DDS overview by UUID or internal reference. The V2 Submission/Retrieval services were removed (discontinued by the EU on acceptance)

### Enums (src/Enum/)
- **EnvironmentEnum** - ACCEPTANCE vs PRODUCTION (different base URLs, WebServiceClientId)
- **ModeEnum** - ECHO, DUE_DILIGENCE_STATEMENT_V4 (maps to WSDL endpoints)
- **ActivityTypeEnum**, **GeometryTypeEnum**, **WoodHeadingEnum** - Domain-specific enums

### DTOs (src/Dto/)
- Request DTOs (e.g., `V4\SubmitDdsRequest`, `V4\GetDdsRequest`) - use `toArray()` for SOAP calls
- Response DTOs (e.g., `V4\SubmitDdsResponse`, `V4\DdsOverviewResponse`) - use `fromSoap()` static factory
- Type DTOs in `src/Dto/Type/` - shared nested structures like `ProducerType`, `GeometryGeojsonType`, `SpeciesInfoType`, `DescriptorsType`

### Serialization (src/Serializer/)
- Uses JMS Serializer with `IdenticalPropertyNamingStrategy` (preserves camelCase keys)
- `GeoJsonHandler` - Custom handler for GeoJSON geometry serialization
- `JmsSerializationTrait` - Shared `toArray()` implementation for DTOs

### Test Fixtures
- `tests/fixtures/payloads.yaml` - YAML-based test data loaded by functional tests
- Tests use JMS Serializer to hydrate DTOs from fixture data

## Key Patterns

- SoapClient instances are cached per service to skip WSDL fetch on subsequent calls; the WS-Security header is rebuilt on every call
- In WSDL mode, SoapClient orders elements from the schema, not from DTO property order
- V4 DTOs live in `src/Dto/V4`. `DdsCommodityType` takes any HS code as a string; it reuses the descriptor, species and producer types from `src/Dto/Type`
- `BodyIdentity` is declared only by the V4 WSDL, so only `EudrDueDiligenceStatementClient` sends it
- WS-Security headers are generated using `robrichards/wse-php` with PasswordDigest authentication
- EudrEchoClient throws `LogicException` if used in PRODUCTION environment
- Response DTOs always provide a static `fromSoap()` method for hydration from raw SOAP responses
