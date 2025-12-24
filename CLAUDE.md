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
- **EudrSubmissionClient** - Submit, amend, retract DDS statements
- **EudrRetrievalClient** - Query DDS info, statements by identifiers

### Enums (src/Enum/)
- **EnvironmentEnum** - ACCEPTANCE vs PRODUCTION (different base URLs, SSL settings, WebServiceClientId)
- **ModeEnum** - ECHO, SUBMISSION, RETRIEVAL (maps to WSDL endpoints)
- **OperatorTypeEnum**, **ActivityTypeEnum**, **GeometryTypeEnum**, **WoodHeadingEnum** - Domain-specific enums

### DTOs (src/Dto/)
- Request DTOs (e.g., `SubmitDdsRequest`, `GetDdsInfoRequest`) - use `toArray()` for SOAP calls
- Response DTOs (e.g., `SubmitDdsResponse`, `GetDdsInfoResponse`) - use `fromSoap()` static factory
- Type DTOs in `src/Dto/Type/` - nested structures like `StatementType`, `CommodityType`, `OperatorType`, `GeometryGeojsonType`

### Serialization (src/Serializer/)
- Uses JMS Serializer with `IdenticalPropertyNamingStrategy` (preserves camelCase keys)
- `GeoJsonHandler` - Custom handler for GeoJSON geometry serialization
- `JmsSerializationTrait` - Shared `toArray()` implementation for DTOs

### Test Fixtures
- `tests/fixtures/payloads.yaml` - YAML-based test data loaded by functional tests
- Tests use JMS Serializer to hydrate DTOs from fixture data

## Key Patterns

- SoapClient instances are cached per service to skip WSDL fetch on subsequent calls
- WS-Security headers are generated using `robrichards/wse-php` with PasswordDigest authentication
- EudrEchoClient throws `LogicException` if used in PRODUCTION environment
- Response DTOs always provide a static `fromSoap()` method for hydration from raw SOAP responses
