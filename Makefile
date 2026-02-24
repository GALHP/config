include ./conf/Makefile

MKDIR := mkdir
MV := mv
RM := rm
RSYNC := rsync
TAR := tar

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

.PHONY: bun-list
bun-list: ## lists included bun package files
	@archive_file=$$(${BUN} pm pack 2>&1 | grep -oE -m1 '${VENDOR}-${PACKAGE}-${SEMVER_REGEX}\.tgz') \
		&& ${TAR} -tf "$$archive_file" | sed 's/^package\///' \
		&& ${RM} -f "$$archive_file"

.PHONY: bun-pack
bun-pack: ## publishes the bun package to ./.local/@<VENDOR>/<PACKAGE>
	@archive_file=$$(${BUN} pm pack 2>&1 | grep -oE -m1 '${VENDOR}-${PACKAGE}-${SEMVER_REGEX}\.tgz') \
		&& ${RM} -rf ${PWD}/.local/@${VENDOR}/${PACKAGE} \
		&& ${MKDIR} -p ${PWD}/.local/@${VENDOR}/${PACKAGE} \
		&& ${TAR} -xzf "$$archive_file" \
		&& ${RM} -f "$$archive_file" \
		&& ${RSYNC} -a ./package/ ${PWD}/.local/@${VENDOR}/${PACKAGE} \
		&& ${RM} -rf ./package

.PHONY: composer-list
composer-list: ## lists included composer package files
	@archive_file=$$(${COMPOSER} archive 2>&1 | ${GREP} -oE -m1 '${VENDOR}-${PACKAGE}-${SEMVER_REGEX}\.tar') \
		&& ${TAR} -tf "$$archive_file" \
		&& ${RM} -f "$$archive_file"

.PHONY: composer-pack
composer-pack: ## publishes the composer package to ./.local/<VENDOR>/<PACKAGE>
	@archive_file=$$(${COMPOSER} archive 2>&1 | ${GREP} -oE -m1 '${VENDOR}-${PACKAGE}-${SEMVER_REGEX}\.tar') \
		&& ${RM} -rf ${PWD}/.local/${VENDOR}/${PACKAGE} \
		&& ${MKDIR} -p ${PWD}/.local/${VENDOR}/${PACKAGE} \
		&& ${MV} "$$archive_file" ${PWD}/.local/${VENDOR}/${PACKAGE}/ \
		&& ${TAR} -C ${PWD}/.local/${VENDOR}/${PACKAGE} -xf ${PWD}/.local/${VENDOR}/${PACKAGE}/"$$archive_file" \
		&& ${RM} -f ${PWD}/.local/${VENDOR}/${PACKAGE}/"$$archive_file"

.PHONY: pack
pack: bun-pack composer-pack ## runs composer-pack and bun-pack
