include ./conf/Makefile

PHP_UNIT := ${PWD}/vendor/bin/pest
PHP_UNIT_CONFIG := --configuration ${PWD}/conf/phpunit.dist.xml

test: ## runs tests
	@${PHP_UNIT} ${PHP_UNIT_CONFIG} ${ARGS}

test-update: ## runs tests with snapshot update
	@${PHP_UNIT} ${PHP_UNIT_CONFIG} --update-snapshots ${ARGS}

check: rector php-cs-fixer phpstan test ## runs rector, php-cs-fixer, phpstan and phpunit
