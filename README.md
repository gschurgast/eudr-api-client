EUDR API SOAP Client (Minimal, PHP Migration of Node Client)

This repository provides a minimal PHP client that mirrors the structure and behavior of the existing Node.js EUDR SOAP client. It includes native SoapClient wrappers for the European Union Deforestation Regulation (EUDR) SOAP services, endpoint utilities, and functional demos.

What’s included
- Simple Environment enum to switch between ACCEPTANCE and PRODUCTION endpoints.
- Endpoint utilities to infer environment from webServiceClientId (eudr-test → acceptance, eudr-repository → production) and expose supported services/IDs.
- Service clients mirroring Node services:
  - EudrEchoClient (forbidden in PRODUCTION)
  - EudrRetrievalClientV2
  - EudrSubmissionClientV2
- Core EudrSoapClient to construct SoapClient instances with sensible defaults and optional endpoint override and SSL controls.
- Docker setup with PHP 8.2 CLI, Composer, and SOAP extension enabled.
- Functional scripts that list WSDL functions and demonstrate the PHP migration usage.

Requirements
- Use the provided Docker environment (recommended). Running tests via native PHP is not supported in this project.

Quick start with Docker (recommended)
1) Build the image (or let docker compose build it on first run):
   docker compose build

2) Install Composer autoload (first run or after changes to composer.json):
   docker compose run --rm php composer install --no-interaction --prefer-dist

3) Run the original functional WSDL listing test:
   docker compose run --rm php composer run test:wsdl

4) Run the PHP migration demo (mirrors Node client usage):
   - Acceptance (default):
     docker compose run --rm -e ENV=acceptance php composer run demo:migration
   - Production:
     docker compose run --rm -e ENV=production php composer run demo:migration

Environment and credentials
- You can pass credentials via env vars:
  - EUDR_USERNAME, EUDR_PASSWORD, EUDR_CLIENT_ID (defaults to eudr-test), SSL=true|false
- The demo and services can also infer the environment automatically from EUDR_CLIENT_ID (eudr-repository → production; eudr-test → acceptance). You can still force ENV explicitly via the ENV variable.

Notes
- Echo service is automatically disallowed in PRODUCTION; attempting to use it will result in a LogicException. The functional demo/test will skip the Echo service in PRODUCTION and report it as skipped.
- The functional scripts only list SOAP operations exposed by the WSDL; they do not perform authenticated or state-changing calls.

How it works
- Environment enum builds the correct base host for the selected environment and joins it with the service path.
- EudrSoapClient centralizes SoapClient creation via a helper that sets sensible defaults and allows forbidding certain services in PRODUCTION (e.g., Echo). It also allows credential and endpoint override options.
- PHP services (EudrEchoClient, EudrRetrievalClientV2, EudrSubmissionClientV2) expose listFunctions() and thin method wrappers for common operations, plus a generic call() method.

Extending
- WS-Security (UsernameToken, signatures, etc.) is not implemented. If your integration requires WS-Security, you will need to extend the SoapClient options or implement a SOAP header/signature layer.
- You can pass extra options to the service constructors, for example:
  $client = (new \src\Services\EudrRetrievalClientV2([
      'username' => 'user',
      'password' => 'pass',
      'webServiceClientId' => 'eudr-test',
      'ssl' => true, // set false to disable peer verification (dev only)
  ]));

Troubleshooting
- SSL/certificates: The endpoints require HTTPS. Ensure your container or host trusts the certificate store (Debian/Alpine CA packages installed). The provided Docker image includes ca-certificates.
- Network access: Corporate proxies or firewalls may block outbound HTTPS to the EUDR endpoints. Configure proxy settings if needed.
- ext-soap missing: The Docker image includes ext-soap. If you extend the image, ensure the extension remains enabled.

License
- This repository is provided as-is, without warranty. Use at your own risk.
