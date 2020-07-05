#!/usr/bin/env bash

# Run phpcs with PHPCompatibility sniffs.

vendor/bin/phpcs -p email-log.php --standard=PHPCompatibility --runtime-set testVersion 5.2
vendor/bin/phpcs -p *.php --standard=PHPCompatibility --runtime-set testVersion 5.6-7.2
vendor/bin/phpcs -p include/ --standard=PHPCompatibility --runtime-set testVersion 5.6-7.2
