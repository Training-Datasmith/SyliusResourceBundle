#!/usr/bin/env bash
# Cloud agent: PHPUnit with optional integration packages (see .github/workflows/build.yml).
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"
symfony_require="${SYLIUS_RESOURCE_SYMFONY_REQUIRE:-7.4.*}"
orm_constraint="${SYLIUS_RESOURCE_ORM:-3.*}"
composer_flags="${SYLIUS_RESOURCE_COMPOSER_FLAGS:---no-scripts --prefer-stable --prefer-dist}"

if ! php -m 2>/dev/null | grep -qi '^mongodb$'; then
  export DEBIAN_FRONTEND=noninteractive
  sudo apt-get update -qq
  php_ver="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
  if apt-cache show "php${php_ver}-mongodb" &>/dev/null 2>&1; then
    sudo apt-get install -y --no-install-recommends "php${php_ver}-mongodb"
  else
    sudo apt-get install -y --no-install-recommends php-mongodb || true
  fi
fi

cd "$repo_root"

composer config extra.symfony.require "${symfony_require}"
(composer config extra.symfony.require "${symfony_require}" --working-dir=src/Component)

composer require --dev "doctrine/orm:${orm_constraint}" --no-update --no-scripts
(composer require --dev "doctrine/orm:${orm_constraint}" --no-update --no-scripts --working-dir=src/Component)

composer require --dev --no-update --no-scripts \
  behat/transliterator:^1.2 \
  gedmo/doctrine-extensions:^3.17.1 \
  friendsofsymfony/rest-bundle:^3.7 \
  willdurand/hateoas-bundle:^2.5 \
  jms/serializer-bundle:^5.5 \
  winzou/state-machine-bundle:^0.6.2 \
  symfony/workflow:7.4.* \
  doctrine/mongodb-odm-bundle:^5.0

(composer require --dev doctrine/mongodb-odm:^2.8 --no-update --no-scripts --working-dir=src/Component)

composer update ${composer_flags}
(composer update ${composer_flags} --working-dir=src/Component)

(cd tests/Application && bin/console doctrine:schema:create)
