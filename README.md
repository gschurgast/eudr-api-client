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
- Configure credentials via environment variables (recommended for security):
  - EUDR_USERNAME
  - EUDR_PASSWORD
- The WebServiceClientId is provided per-environment by default (acceptance → eudr-test, production → eudr-repository).
- Example (shell):
  export EUDR_USERNAME="your-username"
  export EUDR_PASSWORD="your-password"

Usage
- Instantiate the high-level client; it will load credentials from your environment or the project .env file:
  $client = new \src\Services\EudrClient();
  // or pass them explicitly
  $client = new \src\Services\EudrClient('user', 'pass');

Docker Compose
- docker-compose.yml passes EUDR_USERNAME and EUDR_PASSWORD from your host env into the container. Ensure they are set before running commands.

Notes
- Echo service is automatically disallowed in PRODUCTION; attempting to use it will result in a LogicException.
- The functional demo performs authenticated calls with WS-Security UsernameToken. Provide valid credentials to avoid SOAP Faults.

How it works
- Environment enum builds the correct base host for the selected environment and joins it with the service path.
- BaseSoapService constructs SoapClient and injects WS-Security headers (Timestamp + UsernameToken with PasswordDigest). Credentials are provided to services via setUsername()/setPassword() by the EudrClient, which can load them from env/.env.
- PHP services (EudrEchoClient, EudrRetrievalClient, EudrSubmissionClient) implement thin wrappers for SOAP operations.

Extending
- If you use a framework, you can set credentials on the service instances from your configuration or secrets manager before making calls.

Troubleshooting
- SSL/certificates: The endpoints require HTTPS. Ensure your container or host trusts the certificate store (Debian/Alpine CA packages installed). The provided Docker image includes ca-certificates.
- Network access: Corporate proxies or firewalls may block outbound HTTPS to the EUDR endpoints. Configure proxy settings if needed.
- ext-soap missing: The Docker image includes ext-soap. If you extend the image, ensure the extension remains enabled.

License
- This repository is provided as-is, without warranty. Use at your own risk.
