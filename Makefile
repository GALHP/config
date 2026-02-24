include ./conf/Makefile

PHP_UNIT := ${PWD}/vendor/bin/pest
PHP_UNIT_CONFIG := --configuration ${PWD}/conf/phpunit.dist.xml

.PHONY: test
test: ## runs tests
	@${PHP_UNIT} ${PHP_UNIT_CONFIG} ${ARGS}

.PHONY: test-update
test-update: ## runs tests with snapshot update
	@${PHP_UNIT} ${PHP_UNIT_CONFIG} --update-snapshots ${ARGS}

.PHONY: check
check: rector php-cs-fixer phpstan test ## runs rector, php-cs-fixer, phpstan and phpunit
