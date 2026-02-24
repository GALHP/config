PWD ?= .

MAKE ?= make
AWK ?= awk
GREP ?= grep

PHP_STAN ?= ${PWD}/vendor/bin/phpstan analyze --memory-limit=-1
PHP_STAN_CONFIG ?= --configuration ${PWD}/conf/phpstan.neon

PHP_CS_FIXER ?= ${PWD}/vendor/bin/php-cs-fixer
PHP_CS_FIXER_CONFIG ?= --config ${PWD}/conf/.php-cs-fixer.php

RECTOR ?= ${PWD}/vendor/bin/rector process
RECTOR_CONFIG ?= --config ${PWD}/conf/rector.php

ARGS ?= $(wordlist 2,$(words ${MAKECMDGOALS}),${MAKECMDGOALS})

$(if $(findstring :,${ARGS}),,$(eval ${ARGS}:;@:))

PREFIX ?= $(if $(DEBUG),,@)

.PHONY: help

help: ## show this help
	${PREFIX}${AWK} 'BEGIN {FS = ":.*##"; printf "\033[4;37mUsage\033[0m\n  \033[1;37mmake\033[0m \033[1;35m<target>\033[0m\n\n\033[4;37mTargets\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[1;35m%-20s\033[0m \033[30m%s\033[0m\n", $$1, $$2 }' ${MAKEFILE_LIST}

phpstan: ## runs phpstan analyze
	${PREFIX}${PHP_STAN} ${PHP_STAN_CONFIG} ${ARGS}

phpstan-debug: ## runs phpstan analyze in debug mode
	${PREFIX}${PHP_STAN} --debug ${PHP_STAN_CONFIG} ${ARGS}

phpstan-raw: ## runs phpstan analyze with raw output
	${PREFIX}${PHP_STAN} --error-format=raw ${PHP_STAN_CONFIG} ${ARGS}

phpstan-list: ## lists processed phpstan files
	${PREFIX}${MAKE} phpstan-raw -- --debug 2>&1 | ${GREP} -oP "(?<=${PWD}/).+\.php$$"

php-cs-fixer: ## runs php-cs-fixer fix
	${PREFIX}${PHP_CS_FIXER} fix -v --show-progress=dots ${PHP_CS_FIXER_CONFIG} ${ARGS}

php-cs-fixer-list: ## lists processed php-cs-fixer files
	${PREFIX}${PHP_CS_FIXER} list-files ${PHP_CS_FIXER_CONFIG} 2>&1 | ${GREP} -oP "(?<=\./).+\.php"

rector: ## runs rector process
	${PREFIX}${RECTOR} ${RECTOR_CONFIG} ${ARGS}

rector-debug: ## runs rector process in debug mode
	${PREFIX}${RECTOR} ${RECTOR_CONFIG} --debug ${ARGS}

rector-dry-run: ## runs rector process in dry run mode
	${PREFIX}${RECTOR} ${RECTOR_CONFIG} --dry-run ${ARGS}

rector-list: ## lists processed rector files
	${PREFIX}${RECTOR} ${RECTOR_CONFIG} --debug --dry-run | ${GREP} -oP "(?<=\[file\] ${PWD}/).+\.php"

%:
	@:

-include ${PWD}/.local/Makefile
