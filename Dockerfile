# Simple PHP (CLI) image with SOAP extension enabled for this project
FROM php:8.2-cli

# Install dependencies and enable SOAP extension
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libxml2-dev \
        ca-certificates \
        git \
        unzip \
        curl \
    && docker-php-ext-install soap \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (global)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy sources (for image builds); when using docker-compose we will mount the folder
COPY . /app

# Default command prints PHP version; override with `docker compose run --rm php composer --version`
CMD ["php", "-v"]
