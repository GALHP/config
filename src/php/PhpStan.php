<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use Brnshkr\Config\PhpStan\Rule\ApiOrInternalTagRule;
use Brnshkr\Config\PhpStan\Rule\BoolishPrefixRule;
use Brnshkr\Config\PhpStan\Rule\IgnoreDirectiveRule;
use Brnshkr\Config\PhpStan\Rule\InternalUsageRule;
use Brnshkr\Config\PhpStan\Rule\NoNamedArgumentsTagRule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PhpCsFixer\Finder as PhpCsFixerFinder;
use PhpParser\Node;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\String\AbstractString;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symplify\PHPStanRules\Rules as SymplifyPhpStanRules;

use function array_any;
use function array_filter;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function class_exists;
use function explode;
use function in_array;
use function interface_exists;
use function is_executable;
use function is_string;
use function sprintf;
use function Symfony\Component\String\s;

use const PATH_SEPARATOR;

Module::warnMissingPackages(Module::MODULE_PHP_STAN);

/**
 * @api
 *
 * @no-named-arguments
 *
 * @phpstan-type Service array{
 *     class: class-string,
 *     tags?: self::RULE_TAG,
 *     arguments?: array<array-key, mixed>,
 * }
 * @phpstan-type Config array{
 *     includes: list<string>,
 *     parameters: array<string, mixed>,
 *     rules: list<class-string>,
 *     services: list<Service>,
 * }
 */
final class PhpStan
{
    public const string EDITOR_VSCODE   = 'vscode';
    public const string EDITOR_PHPSTORM = 'phpstorm';

    private const array EDITORS = [
        self::EDITOR_VSCODE => [
            'command' => 'code',
            'url'     => [
                'default' => 'vscode://file/%s/%s:%s',
                'wsl'     => 'vscode://vscode-remote/wsl+%s/%s/%s:%s',
            ],
        ],
        self::EDITOR_PHPSTORM => [
            'command' => 'pstorm',
            'url'     => 'phpstorm://open?file=%s/%s&line=%s',
        ],
    ];

    private const array RULE_TAG = ['phpstan.rules.rule'];

    private const string PLACEHOLDER_CWD  = '%currentWorkingDirectory%';
    private const string PLACEHOLDER_FILE = '%%file%%';
    private const string PLACEHOLDER_LINE = '%%line%%';

    /**
     * @param Config $config
     */
    private function __construct(
        private array $config = [
            'includes'   => [],
            'parameters' => [],
            'rules'      => [],
            'services'   => [],
        ],
    ) {}

    /**
     * @template TAsInstance of bool
     *
     * @param TAsInstance $asInstance
     *
     * @return (TAsInstance is true ? self : Config)
     *
     * @throws DirectoryNotFoundException
     * @throws RuntimeException
     */
    public static function getConfig(?Finder $finder = null, bool $asInstance = false): self|array
    {
        $finder ??= new Finder();

        $phpStanConfig = new self()
            ->setLevel('max')
            ->setPaths(PhpFileFinder::getDirectoryPaths($finder), [
                ...PhpFileFinder::EXCLUDED_DIRECTORIES,
                ...PhpFileFinder::EXCLUDED_PATHS,
                'config/preload.php',
            ])
            ->setTemporaryDirectory('.cache/phpstan.cache')
            ->setParameters([
                'editorUrl'                                          => self::getEditorUrl(),
                'editorUrlTitle'                                     => '%%relFile%%:%%line%%',
                'errorFormat'                                        => Module::isPackageInstalled(Module::PACKAGE_PHP_STAN_ERROR_FORMATTER) ? 'ticketswap' : null,
                'checkBenevolentUnionTypes'                          => true,
                'checkImplicitMixed'                                 => true,
                'checkMissingCallableSignature'                      => true,
                'checkMissingOverrideMethodAttribute'                => true,
                'checkMissingOverridePropertyAttribute'              => true,
                'checkStrictPrintfPlaceholderTypes'                  => true,
                'checkTooWideReturnTypesInProtectedAndPublicMethods' => true,
                'checkTooWideThrowTypesInProtectedAndPublicMethods'  => false,
                'rememberPossiblyImpureFunctionValues'               => false,
                'reportAlwaysTrueInLastCondition'                    => true,
                'reportAnyTypeWideningInVarTag'                      => true,
                'reportNonIntStringArrayKey'                         => true,
                'reportPossiblyNonexistentConstantArrayOffset'       => true,
                'reportPossiblyNonexistentGeneralArrayOffset'        => true,
            ])
            ->setFeatureToggles([
                'checkParameterCastableToNumberFunctions'     => true,
                'reportPreciseLineForUnusedFunctionParameter' => true,
                'stricterFunctionMap'                         => true,
            ])
            ->setExceptions([
                'uncheckedExceptionRegexes' => [
                    '/\\\Exception\\\UnreachableException$/',
                ],
                'check' => [
                    'missingCheckedExceptionInThrows' => true,
                    'throwTypeCovariance'             => true,
                    'tooWideImplicitThrowType'        => true,
                    'tooWideThrowType'                => true,
                ],
            ])
            ->setIgnoredErrors([
                [
                    'message'         => '/^Short ternary operator is not allowed. Use null coalesce operator if applicable or consider using long ternary.$/',
                    'reportUnmatched' => false,
                ],
            ])
            ->setRules([
                ApiOrInternalTagRule::class,
                BoolishPrefixRule::class,
                IgnoreDirectiveRule::class,
                NoNamedArgumentsTagRule::class,
                self::configureRule(InternalUsageRule::class, [
                    'allowedDeclaringNamespaces' => [
                        '/^Symfony\\\Component\\\Console\\\Descriptor/',
                    ],
                    'allowedCallingNamespaces' => [
                        '/^Brnshkr\\\Config\\\Tests/',
                    ],
                ]),
            ])
        ;

        if (Module::isPackageInstalled(Module::PACKAGE_PHP_STAN_STRICT_RULES)) {
            $phpStanConfig->setStrictRules([
                'allRules' => true,
            ]);
        }

        if (Module::isPackageInstalled(Module::PACKAGE_TYPE_PERFECT)) {
            $phpStanConfig->setTypePerfect([
                'narrow_return'   => true,
                'no_mixed'        => true,
                'null_over_false' => true,
            ]);
        }

        if (Module::isPackageInstalled(Module::PACKAGE_PHP_STAN_RULES)) {
            $phpStanConfig->setRules(self::getSymplifyRules());
        }

        return $asInstance ? $phpStanConfig : $phpStanConfig->toArray();
    }

    /**
     * @return Config
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * @param list<string> $includes
     */
    public function setIncludes(array $includes): self
    {
        $this->config['includes'] = array_values(array_unique([...$this->config['includes'], ...$includes]));

        return $this;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->config['parameters'] = array_merge($this->config['parameters'], $parameters);

        return $this;
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->config['parameters'][$key] = $value;

        return $this;
    }

    /**
     * @param list<class-string|Service> $rules
     */
    public function setRules(array $rules): self
    {
        $this->config['rules']    = [...array_values(array_unique([...$this->config['rules'], ...array_filter($rules, is_string(...))]))];
        $this->config['services'] = [...$this->config['services'], ...array_filter($rules, is_array(...))];

        return $this;
    }

    /**
     * @param list<class-string> $rules
     */
    public function removeRules(array $rules): self
    {
        $this->config['rules'] = array_values(array_filter(
            $this->config['rules'],
            static fn (string $existingRule): bool => !in_array($existingRule, $rules, true),
        ));

        $this->config['services'] = array_values(array_filter(
            $this->config['services'],
            static fn (array $existingService): bool => !in_array($existingService['class'], $rules, true),
        ));

        return $this;
    }

    /**
     * @param int<0, 10>|'max' $level
     *
     * @see https://phpstan.org/user-guide/rule-levels
     */
    public function setLevel(int|string $level): self
    {
        return $this->setParameter('level', $level);
    }

    /**
     * @param list<string> $paths
     * @param list<string> $excludedPaths
     */
    public function setPaths(array $paths, array $excludedPaths = []): self
    {
        $this->setParameter('paths', $paths);

        if ($excludedPaths !== []) {
            $this->setExcludedPaths($excludedPaths);
        }

        return $this;
    }

    /**
     * @param list<string> $excludedPaths
     */
    public function setExcludedPaths(array $excludedPaths): self
    {
        return $this->setParameter('excludePaths', $excludedPaths);
    }

    /**
     * @param list<string> $bootstrapFiles
     */
    public function setBootstrapFiles(array $bootstrapFiles): self
    {
        return $this->setParameter('bootstrapFiles', $bootstrapFiles);
    }

    /**
     * @see https://phpstan.org/config-reference#caching
     */
    public function setTemporaryDirectory(?string $temporaryDirectory): self
    {
        return $this->setParameter('tmpDir', $temporaryDirectory);
    }

    /**
     * @param list<string|array{
     *     message: string,
     *     identifier?: string,
     *     count?: int,
     *     path?: string,
     *     reportUnmatched?: bool,
     * }> $ignoredErrors
     *
     * @see https://phpstan.org/user-guide/ignoring-errors#ignoring-in-configuration-file
     */
    public function setIgnoredErrors(string|array $ignoredErrors): self
    {
        return $this->setParameter('ignoreErrors', $ignoredErrors);
    }

    /**
     * @param array<string, bool> $featureToggles
     */
    public function setFeatureToggles(array $featureToggles): self
    {
        return $this->setParameter('featureToggles', $featureToggles);
    }

    /**
     * @param array<string, mixed> $exceptions
     *
     * @see https://phpstan.org/config-reference#exceptions
     */
    public function setExceptions(array $exceptions): self
    {
        return $this->setParameter('exceptions', $exceptions);
    }

    /**
     * @param array<string, bool> $strictRules
     *
     * @see https://github.com/phpstan/phpstan-strict-rules
     *
     * @throws RuntimeException
     */
    public function setStrictRules(array $strictRules): self
    {
        Module::warnMissingPackages(Module::PACKAGE_PHP_STAN_STRICT_RULES);

        return $this->setParameter('strictRules', $strictRules);
    }

    /**
     * @param array<string, bool> $options
     *
     * @see https://github.com/rectorphp/type-perfect
     *
     * @throws RuntimeException
     */
    public function setTypePerfect(array $options): self
    {
        Module::warnMissingPackages(Module::PACKAGE_TYPE_PERFECT);

        return $this->setParameter('type_perfect', $options);
    }

    /**
     * @param self::EDITOR_* $editor
     */
    public function setEditor(string $editor): self
    {
        return $this->setParameter('editorUrl', self::getEditorUrl($editor));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @see https://github.com/phpstan/phpstan-symfony
     *
     * @throws RuntimeException
     */
    public function setSymfony(array $options): self
    {
        Module::warnMissingPackages(Module::PACKAGE_PHP_STAN_SYMFONY);

        return $this->setParameter('symfony', $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws RuntimeException
     *
     * @see https://github.com/phpstan/phpstan-doctrine
     */
    public function setDoctrine(array $options): self
    {
        Module::warnMissingPackages(Module::PACKAGE_PHP_STAN_DOCTRINE);

        return $this->setParameter('doctrine', $options);
    }

    /**
     * @template TClass of object
     *
     * @param class-string<TClass> $class
     * @param array<array-key, mixed> $arguments
     *
     * @return Service
     */
    public static function configureRule(string $class, array $arguments = []): array
    {
        return [
            'class'     => $class,
            'tags'      => self::RULE_TAG,
            'arguments' => $arguments,
        ];
    }

    /**
     * @return array<class-string, class-string>
     *
     * @throws RuntimeException
     */
    public static function getPreferredClassesMap(): array
    {
        Module::warnMissingPackages(Module::PACKAGE_PHP_STAN_RULES);

        $preferredClassesMap = [
            // NOTICE: Explicit use of 'DateTime' as a string to prevent php-cs-fixer from fixing this to 'DateTimeImmutable'
            'DateTime' => DateTimeImmutable::class,
            // @phpstan-ignore symplify.preferredClass (We need to disable this rules here of course)
            SplFileInfo::class => SymfonySplFileInfo::class,
        ];

        /** @disregard P1009 nesbot/carbon is not a dependency of brnshkr/config */
        // @phpstan-ignore symplify.forbiddenFuncCall (nesbot/carbon is not a dependency of brnshkr/config)
        if (class_exists(Carbon::class)) {
            /** @disregard P1009 nesbot/carbon is not a dependency of brnshkr/config */
            // @phpstan-ignore class.notFound, class.notFound (nesbot/carbon is not a dependency of brnshkr/config)
            $preferredClassesMap[Carbon::class] = CarbonImmutable::class;
        }

        // @phpstan-ignore symplify.preferredClass, symplify.forbiddenFuncCall (We need to disable both these rules here of course)
        if (class_exists(PhpCsFixerFinder::class)) {
            // @phpstan-ignore symplify.preferredClass (We need to disable this rules here of course)
            $preferredClassesMap[PhpCsFixerFinder::class] = Finder::class;
        }

        return $preferredClassesMap;
    }

    /**
     * @return list<class-string|Service>
     *
     * @throws RuntimeException
     */
    private static function getSymplifyRules(): array
    {
        return [
            SymplifyPhpStanRules\Complexity\ForbiddenArrayMethodCallRule::class,
            SymplifyPhpStanRules\Complexity\ForeachCeptionRule::class,
            SymplifyPhpStanRules\Complexity\NoArrayMapWithArrayCallableRule::class,
            SymplifyPhpStanRules\Complexity\NoJustPropertyAssignRule::class,
            SymplifyPhpStanRules\Doctrine\NoDoctrineListenerWithoutContractRule::class,
            SymplifyPhpStanRules\Doctrine\NoGetRepositoryOnServiceRepositoryEntityRule::class,
            SymplifyPhpStanRules\Doctrine\NoGetRepositoryOutsideServiceRule::class,
            SymplifyPhpStanRules\Doctrine\NoParentRepositoryRule::class,
            SymplifyPhpStanRules\Doctrine\NoRepositoryCallInDataFixtureRule::class,
            SymplifyPhpStanRules\Doctrine\RequireQueryBuilderOnRepositoryRule::class,
            SymplifyPhpStanRules\Doctrine\RequireServiceRepositoryParentRule::class,
            SymplifyPhpStanRules\Domain\RequireAttributeNamespaceRule::class,
            SymplifyPhpStanRules\Domain\RequireExceptionNamespaceRule::class,
            SymplifyPhpStanRules\Enum\RequireUniqueEnumConstantRule::class,
            SymplifyPhpStanRules\Explicit\ExplicitClassPrefixSuffixRule::class,
            SymplifyPhpStanRules\Explicit\NoMissingVariableDimFetchRule::class,
            SymplifyPhpStanRules\Explicit\NoProtectedClassStmtRule::class,
            SymplifyPhpStanRules\ForbiddenExtendOfNonAbstractClassRule::class,
            SymplifyPhpStanRules\ForbiddenMultipleClassLikeInOneFileRule::class,
            SymplifyPhpStanRules\ForbiddenStaticClassConstFetchRule::class,
            SymplifyPhpStanRules\NoDynamicNameRule::class,
            SymplifyPhpStanRules\NoEntityOutsideEntityNamespaceRule::class,
            SymplifyPhpStanRules\NoGlobalConstRule::class,
            SymplifyPhpStanRules\NoMissnamedDocTagRule::class,
            SymplifyPhpStanRules\NoReferenceRule::class,
            SymplifyPhpStanRules\PHPUnit\NoAssertFuncCallInTestsRule::class,
            SymplifyPhpStanRules\PHPUnit\NoMockObjectAndRealObjectPropertyRule::class,
            SymplifyPhpStanRules\PHPUnit\PublicStaticDataProviderRule::class,
            SymplifyPhpStanRules\PreventParentMethodVisibilityOverrideRule::class,
            SymplifyPhpStanRules\Rector\AvoidFeatureSetAttributeInRectorRule::class,
            SymplifyPhpStanRules\Rector\PreferDirectIsNameRule::class,
            SymplifyPhpStanRules\RequireAttributeNameRule::class,
            SymplifyPhpStanRules\StringFileAbsolutePathExistsRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\AlreadyRegisteredAutodiscoveryServiceRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\NoBundleResourceConfigRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\NoDuplicateArgAutowireByTypeRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\NoDuplicateArgsAutowireByTypeRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\NoServiceSameNameSetClassRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\NoSetClassServiceDuplicationRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\PreferAutowireAttributeOverConfigParamRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\ServicesExcludedDirectoryMustExistRule::class,
            SymplifyPhpStanRules\Symfony\ConfigClosure\TaggedIteratorOverRepeatedServiceCallRule::class,
            SymplifyPhpStanRules\Symfony\NoAbstractControllerConstructorRule::class,
            SymplifyPhpStanRules\Symfony\NoBareAndSecurityIsGrantedContentsRule::class,
            SymplifyPhpStanRules\Symfony\NoClassLevelRouteRule::class,
            SymplifyPhpStanRules\Symfony\NoConstructorAndRequiredTogetherRule::class,
            SymplifyPhpStanRules\Symfony\NoControllerMethodInjectionRule::class,
            SymplifyPhpStanRules\Symfony\NoGetDoctrineInControllerRule::class,
            SymplifyPhpStanRules\Symfony\NoGetInCommandRule::class,
            SymplifyPhpStanRules\Symfony\NoGetInControllerRule::class,
            SymplifyPhpStanRules\Symfony\NoListenerWithoutContractRule::class,
            SymplifyPhpStanRules\Symfony\NoRouteTrailingSlashPathRule::class,
            SymplifyPhpStanRules\Symfony\NoRoutingPrefixRule::class,
            SymplifyPhpStanRules\Symfony\NoServiceAutowireDuplicateRule::class,
            SymplifyPhpStanRules\Symfony\NoStringInGetSubscribedEventsRule::class,
            SymplifyPhpStanRules\Symfony\RequiredOnlyInAbstractRule::class,
            SymplifyPhpStanRules\Symfony\RequireIsGrantedEnumRule::class,
            SymplifyPhpStanRules\Symfony\RequireRouteNameToGenerateControllerRouteRule::class,
            SymplifyPhpStanRules\Symfony\SingleArgEventDispatchRule::class,
            SymplifyPhpStanRules\UppercaseConstantRule::class,
            self::configureRule(SymplifyPhpStanRules\ForbiddenNodeRule::class, [
                'forbiddenNodes' => self::getForbiddenNodes(),
            ]),
            self::configureRule(SymplifyPhpStanRules\PreferredClassRule::class, [
                'oldToPreferredClasses' => self::getPreferredClassesMap(),
            ]),
            self::configureRule(SymplifyPhpStanRules\ForbiddenFuncCallRule::class, [
                'forbiddenFunctions' => self::getForbiddenFunctions(),
            ]),
        ];
    }

    /**
     * @return list<class-string>
     */
    private static function getForbiddenNodes(): array
    {
        return [
            Node\Expr\Empty_::class,
            Node\Expr\ErrorSuppress::class,
            Node\Expr\PostDec::class,
            Node\Expr\PostInc::class,
            Node\Expr\PreDec::class,
            Node\Expr\PreInc::class,
            Node\InterpolatedStringPart::class,
            Node\Scalar\InterpolatedString::class,
            Node\Stmt\Switch_::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getForbiddenFunctions(): array
    {
        $forbiddenFunctions = [
            'eval'              => 'Usage of this function is strongly discouraged. If using this function is really the only option, please disable this rule for this line.',
            'compact'           => 'Explicitly assign to keys in the array.',
            'extract'           => 'Explicitly define variables for the entries of the array.',
            'method_exists'     => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'property_exists'   => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'class_exists'      => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'interface_exists'  => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'trait_exists'      => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'enum_exists'       => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'spl_autoload'      => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'spl_autoload_*'    => 'Usage of this function is discouraged. If using this function is really the only option, please disable this rule for this line.',
            'var_dump'          => 'Please remove all debug functions. Use a logger if needed.',
            'dd'                => 'Please remove all debug functions. Use a logger if needed.',
            'dump'              => 'Please remove all debug functions. Use a logger if needed.',
            'debug'             => 'Please remove all debug functions. Use a logger if needed.',
            'file_get_contents' => sprintf('Use "%s::readFile()" instead.', Filesystem::class),
            'file_put_contents' => sprintf('Use "%1$s::dumpFile()" or "%1$s::appendToFile()" instead.', Filesystem::class),
        ];

        // @phpstan-ignore symplify.forbiddenFuncCall (This is the only way to achieve what we need here)
        if (class_exists(AbstractString::class)) {
            $stringFunction = s(AbstractString::class)
                ->beforeLast('\\')
                ->append('\s')
                ->toString()
            ;

            $forbiddenFunctions = [
                ...$forbiddenFunctions,
                'ucfirst'               => sprintf('Use "%s::title()" instead.', $stringFunction),
                'mb_ucfirst'            => sprintf('Use "%s::title()" instead.', $stringFunction),
                'ucwords'               => sprintf('Use "%s::title()" instead.', $stringFunction),
                'str_pad'               => sprintf('Use "%s::{padBoth,padEnd,padStart}()" instead.', $stringFunction),
                'mb_str_pad'            => sprintf('Use "%s::{padBoth,padEnd,padStart}()" instead.', $stringFunction),
                'trim'                  => sprintf('Use "%s::trim()" instead.', $stringFunction),
                'mb_trim'               => sprintf('Use "%s::trim()" instead.', $stringFunction),
                'ltrim'                 => sprintf('Use "%s::trimStart()" instead.', $stringFunction),
                'mb_ltrim'              => sprintf('Use "%s::trimStart()" instead.', $stringFunction),
                'rtrim'                 => sprintf('Use "%s::trimEnd()" instead.', $stringFunction),
                'mb_rtrim'              => sprintf('Use "%s::trimEnd()" instead.', $stringFunction),
                'str_split'             => sprintf('Use "%s::chunk()" instead.', $stringFunction),
                'mb_str_split'          => sprintf('Use "%s::chunk()" instead.', $stringFunction),
                'mb_split'              => sprintf('Use "%s::split()" instead.', $stringFunction),
                'strlen'                => sprintf('Use "%s::length()" instead.', $stringFunction),
                'mb_strlen'             => sprintf('Use "%s::length()" instead.', $stringFunction),
                'strtolower'            => sprintf('Use "%s::lower()" instead.', $stringFunction),
                'mb_strtolower'         => sprintf('Use "%s::lower()" instead.', $stringFunction),
                'strtoupper'            => sprintf('Use "%s::upper()" instead.', $stringFunction),
                'mb_strtoupper'         => sprintf('Use "%s::upper()" instead.', $stringFunction),
                'substr'                => sprintf('Use "%s::slice()" instead.', $stringFunction),
                'mb_substr'             => sprintf('Use "%s::slice()" instead.', $stringFunction),
                'str_contains'          => sprintf('Use "%s::containsAny()" instead.', $stringFunction),
                'str_starts_with'       => sprintf('Use "%s::startsWith()" instead.', $stringFunction),
                'str_ends_with'         => sprintf('Use "%s::endsWith()" instead.', $stringFunction),
                'str_replace'           => sprintf('Use "%s::replace()" instead.', $stringFunction),
                'str_ireplace'          => sprintf('Use "%s::replace()" instead.', $stringFunction),
                'substr_replace'        => sprintf('Use "%s::replace()" instead.', $stringFunction),
                'str_repeat'            => sprintf('Use "%s::repeat()" instead.', $stringFunction),
                'str*'                  => sprintf('Use "%s" instead. If using this function is really the only option, please disable this rule for this line.', $stringFunction),
                'mb_str*'               => sprintf('Use "%s instead.', $stringFunction),
                'preg_match_all'        => sprintf('Use "%s::match()" instead.', $stringFunction),
                'preg_match'            => sprintf('Use "%s::match()" instead.', $stringFunction),
                'preg_replace_callback' => sprintf('Use "%s::replaceMatches()" instead.', $stringFunction),
                'preg_replace'          => sprintf('Use "%s::replaceMatches()" instead.', $stringFunction),
            ];
        }

        /** @disregard P1009 symfony/http-client-contracts is not a dependency of brnshkr/config */
        // @phpstan-ignore symplify.forbiddenFuncCall (symfony/http-client-contracts is not a dependency of brnshkr/config)
        if (interface_exists(HttpClientInterface::class)) {
            // @phpstan-ignore class.notFound (symfony/http-client-contracts is not a dependency of brnshkr/config)
            $forbiddenFunctions['curl_*'] = sprintf('Use an implementation of "%s" or any alternative HTTP client instead.', HttpClientInterface::class);
        }

        /** @disregard P1009 symfony/serializer is not a dependency of brnshkr/config */
        // @phpstan-ignore symplify.forbiddenFuncCall (symfony/serializer is not a dependency of brnshkr/config)
        if (class_exists(JsonEncoder::class)) {
            $forbiddenFunctions['json_decode'] = sprintf('Use "%s::decode()" instead.', JsonEncoder::class);
            $forbiddenFunctions['json_encode'] = sprintf('Use "%s::encode()" instead.', JsonEncoder::class);
        }

        return $forbiddenFunctions;
    }

    /**
     * @template TEditor of ?self::EDITOR_*
     *
     * @param TEditor $editor
     *
     * @return (TEditor is null ? ?string : string)
     */
    private static function getEditorUrl(?string $editor = null): ?string
    {
        $environment = array_merge($_SERVER, $_ENV);
        $editor ??= self::getEditor($environment);

        if ($editor === null) {
            $paths = explode(PATH_SEPARATOR, is_string($environment['PATH'] ?? null) ? $environment['PATH'] : '');

            foreach (self::EDITORS as $editorCandidate => $config) {
                if (self::isCommandAvailable($config['command'], $paths)) {
                    $editor = $editorCandidate;

                    break;
                }
            }
        }

        if ($editor === null || !isset(self::EDITORS[$editor])) {
            return null;
        }

        $config = self::EDITORS[$editor];

        if (is_string($config['url'])) {
            return sprintf($config['url'], self::PLACEHOLDER_CWD, self::PLACEHOLDER_FILE, self::PLACEHOLDER_LINE);
        }

        $wslDistro = self::getWslDistroName($environment);

        return $wslDistro === null
            ? sprintf($config['url']['default'], self::PLACEHOLDER_CWD, self::PLACEHOLDER_FILE, self::PLACEHOLDER_LINE)
            : sprintf($config['url']['wsl'], $wslDistro, self::PLACEHOLDER_CWD, self::PLACEHOLDER_FILE, self::PLACEHOLDER_LINE);
    }

    /**
     * @param array<array-key, mixed> $environment
     */
    private static function getEditor(array $environment): ?string
    {
        foreach (array_keys($environment) as $key) {
            $key = (string) $key;

            $editor = match (true) {
                Str::doesStartWith($key, 'VSCODE_')  => self::EDITOR_VSCODE,
                Str::doesStartWith($key, 'PHPSTORM') => self::EDITOR_PHPSTORM,
                default                              => null,
            };

            if ($editor !== null) {
                return $editor;
            }
        }

        return null;
    }

    /**
     * @param array<array-key, mixed> $environment
     */
    private static function getWslDistroName(array $environment): ?string
    {
        return (isset($environment['WSL_DISTRO_NAME']) && is_string($environment['WSL_DISTRO_NAME']))
            ? $environment['WSL_DISTRO_NAME']
            : null;
    }

    /**
     * @param list<string> $paths
     */
    private static function isCommandAvailable(string $command, array $paths): bool
    {
        return array_any(
            $paths,
            static fn (string $path): bool => is_executable(Str::trim($path, '/', 'end') . '/' . $command),
        );
    }
}

return PhpStan::getConfig();
