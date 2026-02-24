include ./conf/Makefile

PHP_UNIT := ${PWD}/vendor/bin/pest
PHP_UNIT_CONFIG := --configuration ${PWD}/conf/phpunit.dist.xml

.PHONY: _------------_
_-----------_: ##
	${PREFIX}${MAKE} -s help

.PHONY: _APP_TARGETS_
_APP_TARGETS_: ##
	${PREFIX}${MAKE} -s help

.PHONY: -------------
-------------: ##
	${PREFIX}${MAKE} -s help

.PHONY: test
test: ## runs tests
	@${PHP_UNIT} ${PHP_UNIT_CONFIG} ${ARGS}

.PHONY: test-update
test-update: ## runs tests with snapshot update
	@${PHP_UNIT} ${PHP_UNIT_CONFIG} --update-snapshots ${ARGS}

.PHONY: check
check: rector php-cs-fixer phpstan test ## runs rector, php-cs-fixer, phpstan and phpunit
