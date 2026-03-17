#==
# brnshkr/config Makefile
# ‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾
# This Makefile provides shared tooling, conventions, and utilities that can be
# included and extended by project-specific Makefiles. It implements a small
# framework around GNU Make to provide:
#   - structured CLI-style commands
#   - argument forwarding to targets
#   - automatic help generation
#   - terminal formatting helpers
#
# Variables and macros prefixed with an underscore ('_') are considered private
# implementation details and are not part of the public API.
#
# Macros that are written is UPPER_SNAKE_CASE are treated as variables.
# Macros that are written in lower_snake_case are treated as functions.
#
# This Makefile implements a custom argument parsing system that allows
# arguments to be passed to specific targets while preserving Make's standard behavior:
#
#   Rules:
#     - Arguments that do NOT match an existing target are treated as arguments
#     - Arguments before the first valid target are passed to ALL invoked targets
#     - Arguments between two targets are passed only to the preceding target
#     - Runs that contain no valid targets trigger the error defined in .DEFAULT target
#
#   Examples of what the above-mentioned looks like:
#     make a               -> runs target a
#     make a b             -> runs targets a and b
#     make a a1 a2 b b1 b2 -> runs target a with arguments a1 and a2 and target b with arguments b1 and b2 (assuming a1, a2, b1 and b2 are not targets by themselves)
#     make ab1 ab2 a a1 b  -> runs target a with arguments ab1, ab2 and a1 and target b with arguments ab1 and ab2 (assuming ab1, ab2 and a1 are not targets by themselves)
#     make no arg exists   -> throws error defined in .DEFAULT target
#
# Any target can be prepended with the DEBUG flag to enable command tracing:
#   - make DEBUG=1 phpstan
#   - DEBUG=1 make phpstan
#
# All make runs can be prepend with the NO_ANSI flag to disable any ANSI escape sequences in the output:
#   - make NO_ANSI=1 help
#   - NO_ANSI=1 make help
#
# The `make help` command automatically generates documentation based on specially formatted comments:
#   Scope headers (control grouping and nesting in the help output):
#     #-- name      —  top-level group (depth 0); all subsequent symbols belong to this group
#     #--- name     —  section (depth 1, nested inside the nearest depth-0 group)
#     #---- name    —  subsection (depth 2)
#     #-----… name  —  subsection (depth N)
#
#     Append 'v' characters to set a minimum verbosity level for everything inside the scope:
#       #---v name    —  contents hidden unless make help v   (verbosity ≥ 1)
#       #---vv name   —  contents hidden unless make help vv  (verbosity ≥ 2)
#       #---vvv name  —  contents hidden unless make help vvv (verbosity ≥ 3)
#
#   Inline description comments (attach metadata to a specific symbol):
#     VARIABLE ?= value#~~ description  —  annotate a variable with a description
#     command: #~~ description          —  annotate a command with a description
#
#     Append 'v' characters after '#' to make the line itself require higher verbosity:
#       command: #~~ description #v  —  hide the command entry unless verbosity >=1
#       VARIABLE ?= value#vv #~~ …   —  hide the variable entry unless verbosity ≥ 2
#       VARIABLE ?= value#~~ … #vvv  —  hide the variable entry unless verbosity ≥ 3
#
#   Description block (shown at the top of the help output under "Description:"):
#     #!! text  —  emit a free-form description line; multiple lines are supported
#
#   Documentation blocks (shown for function macros at higher verbosities):
#     #**      —  open a documentation block
#     #* text  —  body line inside the block (leading '#* ' is stripped)
#     The first non-comment, non-empty line after the block must be a 'define' statement.
#==

#-- brnshkr

#!! Main Makefile of the brnshkr/config package

help: #~~ show this help (optionally with higher verbosity: v/vv/vvv | -v/-vv/-vvv | V=1/V=2/V=3)
	$(DEBUG_PREFIX)$(PRINTF) '$(call text,$(call _escape_newlines,$(LOGO)),$(COLOR_LOGO))'
	$(DEBUG_PREFIX)$(PRINTF) '$(call text,$(call _to_title_case,$(PACKAGE)) ($(strip $(VERSION))),$(COLOR_TITLE))\n\n'
	$(DEBUG_PREFIX)$(PRINTF) '$(call text,Usage:,$(COLOR_SECTION_LABEL))\n  $(call text,make,$(COLOR_USAGE_PROGRAM)) $(call text,<command> [--] [arguments],$(COLOR_USAGE_ARGUMENTS))\n\n'
	$(DEBUG_PREFIX)$(PRINTF) '%s' "$$_HELP_AWK" > /tmp/brnshkr_config_help.awk
	$(DEBUG_PREFIX)$(AWK) \
		-v INDENT="$(INDENT)" \
		-v NO_ANSI="$(NO_ANSI)" \
		-v PWD="$(abspath $(PWD))" \
		-v EDITOR="$(_EDITOR)" \
		-v EDITOR_URL_VSCODE="$(_EDITOR_URL_VSCODE)" \
		-v EDITOR_URL_VSCODE_WSL="$(_EDITOR_URL_VSCODE_WSL)" \
		-v EDITOR_URL_PHPSTORM="$(_EDITOR_URL_PHPSTORM)" \
		-v WSL_DISTRO_NAME="$(or $(WSL_DISTRO_NAME),)" \
		-v VERBOSITY=$(or $(V),$(if $(filter -vvv vvv,$(ARGS)) \
			,3 \
			,$(if $(filter -vv vv,$(ARGS)) \
				,2 \
				,$(if $(filter -v v,$(ARGS)) \
					,1 \
					,0) \
				) \
			) \
		) \
		-f /tmp/brnshkr_config_help.awk \
		$(MAKEFILE_LIST)

# --- includes

_THIS_FILE     := $(abspath $(lastword $(MAKEFILE_LIST)))
_CONF_MAKEFILE := $(filter-out $(_THIS_FILE),$(wildcard $(PWD)/conf/Makefile))

ifneq ($(_CONF_MAKEFILE),)
include $(_CONF_MAKEFILE)
endif

ifneq ($(wildcard $(PWD)/conf/*.mk),)
include $(PWD)/conf/*.mk
endif

ifneq ($(wildcard $(PWD)/conf/make/Makefile),)
include $(PWD)/conf/make/Makefile
endif

ifneq ($(wildcard $(PWD)/conf/make/*.mk),)
include $(PWD)/conf/make/*.mk
endif

ifneq ($(wildcard $(PWD)/.local/Makefile),)
include $(PWD)/.local/Makefile
endif

ifneq ($(wildcard $(PWD)/.local/*.mk),)
include $(PWD)/.local/*.mk
endif

ifneq ($(wildcard $(PWD)/.local/make/Makefile),)
include $(PWD)/.local/make/Makefile
endif

ifneq ($(wildcard $(PWD)/.local/make/*.mk),)
include $(PWD)/.local/make/*.mk
endif

#---v general

PWD          ?= .#vvv #~~ current working directory
SHELL        ?= /bin/sh#vv #~~ shell to use
VENDOR       ?= brnshkr#~~ vendor name
PACKAGE      ?= config#~~ package name
VERSION      ?= 0.0.1-alpha.14#vv #~~ package version
DEBUG        ?= #~~ whether debug mode is enabled
DEBUG_PREFIX ?= $(if $(DEBUG),,@)#vvv #~~ prefix for all commands

#--- phpstan

PHP_STAN        ?= $(PWD)/vendor/bin/phpstan#~~ path to phpstan binary
PHP_STAN_CONFIG ?= $(PWD)/conf/phpstan.php#~~ path to phpstan config
PHP_STAN_FLAGS  ?= --memory-limit=-1 $(if $(DEBUG),-vvv,-vv)#~~ additional flags passed to phpstan

#--- php-cs-fixer

PHP_CS_FIXER        ?= $(PWD)/vendor/bin/php-cs-fixer#~~ path to php-cs-fixer binary
PHP_CS_FIXER_CONFIG ?= $(PWD)/conf/php-cs-fixer.php#~~ path to php-cs-fixer config
PHP_CS_FIXER_FLAGS  ?= --show-progress=dots $(if $(DEBUG),-vvv,-v)#~~ additional flags passed to php-cs-fixer

#--- rector

RECTOR        ?= $(PWD)/vendor/bin/rector process#~~ path to rector binary
RECTOR_CONFIG ?= $(PWD)/conf/rector.php#~~ path to rector config
RECTOR_FLAGS  ?= --memory-limit=-1#~~ additional flags passed to rector

#---v tools

MAKE   ?= make#~~ path to make binary
PRINTF ?= printf#~~ path to printf binary
AWK    ?= awk#~~ path to awk binary

#---vv theming

INDENT  ?= 1#~~ indent used in `make help` output

NO_ANSI         ?= #~~ whether ANSI colors are enabled
_NO_ANSI_OPTION := $(if $(NO_ANSI),--no-ansi)

THEME         ?= #~~ the theme to use
THEME_BRNSHKR := brnshkr
THEME_SYMFONY := symfony

_THEMES := $(THEME_BRNSHKR) \
	$(THEME_SYMFONY)

_THEME_DEFAULT := $(THEME_BRNSHKR)
_THEME         := $(if $(filter $(THEME),$(_THEMES)),$(THEME),$(_THEME_DEFAULT))

EDITOR          ?= #~~ the editor to use
EDITOR_VSCODE   := vscode
EDITOR_PHPSTORM := phpstorm

_EDITORS := $(EDITOR_VSCODE) \
	$(EDITOR_PHPSTORM)

_EDITOR_URL_VSCODE     := vscode://file/%s:%s
_EDITOR_URL_VSCODE_WSL := vscode://vscode-remote/wsl+%s/%s:%s
_EDITOR_URL_PHPSTORM   := phpstorm://open?file=%s&line=%s

ifndef EDITOR
_EDITOR = $(or $(call _editor_from_env),$(call _editor_from_command),$(EDITOR_VSCODE))
else
_EDITOR = $(if $(filter $(EDITOR),$(_EDITORS)),$(EDITOR),$(or $(call _editor_from_env),$(call _editor_from_command),$(EDITOR_VSCODE)))
endif

ifndef LOGO
define LOGO
   ___               __   __
  / _ )_______  ____/ /  / /__ ____
 / _  / __/ _ \\(_--/ _ \\/  ´_// __/
/____/_/ /_//_/___/_//_/_/\\_\\/_/


endef
endif

#---- colors

COLOR_NORMAL  := normal
COLOR_BLACK   := black
COLOR_RED     := red
COLOR_GREEN   := green
COLOR_YELLOW  := yellow
COLOR_BLUE    := blue
COLOR_MAGENTA := magenta
COLOR_CYAN    := cyan
COLOR_WHITE   := white

ifeq ($(_THEME),$(THEME_BRNSHKR))
COLOR_PRIMARY         ?= $(COLOR_CYAN)
COLOR_SECONDARY       ?= $(COLOR_MAGENTA)
COLOR_TERTIARY        ?= $(COLOR_BLACK) $(MODIFIER_BRIGHT)
COLOR_ERROR           ?= $(COLOR_RED)
COLOR_NOTICE          ?= $(COLOR_MAGENTA)
COLOR_LOGO            ?= $(COLOR_SECONDARY)
COLOR_TITLE           ?= $(COLOR_PRIMARY) $(MODIFIER_UNDERLINE)
COLOR_USAGE_ARGUMENTS ?= $(COLOR_PRIMARY)
COLOR_USAGE_PROGRAM   ?= $(COLOR_PRIMARY)
COLOR_SECTION_LABEL   ?= $(COLOR_SECONDARY)
COLOR_COMMAND         ?= $(COLOR_PRIMARY)
COLOR_DESCRIPTION     ?= $(COLOR_TERTIARY)
else ifeq ($(_THEME),$(THEME_SYMFONY))
COLOR_PRIMARY         ?= $(COLOR_GREEN)
COLOR_SECONDARY       ?= $(COLOR_YELLOW)
COLOR_TERTIARY        ?= $(COLOR_NORMAL)
COLOR_ERROR           ?= $(COLOR_RED)
COLOR_NOTICE          ?= $(COLOR_CYAN)
COLOR_LOGO            ?= $(COLOR_SECONDARY)
COLOR_TITLE           ?= $(COLOR_PRIMARY) $(MODIFIER_UNDERLINE)
COLOR_USAGE_PROGRAM   ?= $(COLOR_TERTIARY)
COLOR_USAGE_ARGUMENTS ?= $(COLOR_TERTIARY)
COLOR_SECTION_LABEL   ?= $(COLOR_SECONDARY)
COLOR_COMMAND         ?= $(COLOR_PRIMARY)
COLOR_DESCRIPTION     ?= $(COLOR_TERTIARY)
endif

_COLORS := $(COLOR_BLACK) \
	$(COLOR_RED) \
	$(COLOR_GREEN) \
	$(COLOR_YELLOW) \
	$(COLOR_BLUE) \
	$(COLOR_MAGENTA) \
	$(COLOR_CYAN) \
	$(COLOR_WHITE)

#----vvv modifiers

MODIFIER_NORMAL    := normal
MODIFIER_BRIGHT    := bright
MODIFIER_UNDERLINE := underline
MODIFIER_BOLD      := bold
MODIFIER_REVERSE   := reverse

_MODIFIERS := $(MODIFIER_NORMAL) \
	$(MODIFIER_BRIGHT) \
	$(MODIFIER_UNDERLINE) \
	$(MODIFIER_BOLD) \
	$(MODIFIER_REVERSE)

#---v arguments

_ALL_TARGETS := $(shell $(AWK) \
	'BEGIN { FS=":" } \
	/^[a-zA-Z0-9_.-]+:/ { t=$$1; if (t !~ /^[.]/) $(PRINTF) "%s ", t; }' \
	$(MAKEFILE_LIST) \
)

TARGET := $(firstword $(filter $(_ALL_TARGETS),$(MAKECMDGOALS)))
ARG1 := $(word 2,$(MAKECMDGOALS)) #vv
ARG2 := $(word 3,$(MAKECMDGOALS)) #vv
ARG3 := $(word 4,$(MAKECMDGOALS)) #vv
ARG4 := $(word 5,$(MAKECMDGOALS)) #vv
ARG5 := $(word 6,$(MAKECMDGOALS)) #vv
ARG6 := $(word 7,$(MAKECMDGOALS)) #vv
ARG7 := $(word 8,$(MAKECMDGOALS)) #vv
ARG8 := $(word 9,$(MAKECMDGOALS)) #vv
ARG9 := $(word 10,$(MAKECMDGOALS)) #vv
ARGS = $(strip $(filter-out $(_ALL_TARGETS),$(call _collect_args,$@,$(MAKECMDGOALS))))

#--- phpstan

phpstan: #~~ runs phpstan analyze
	$(DEBUG_PREFIX)$(PHP_STAN) analyze --configuration $(PHP_STAN_CONFIG) $(_NO_ANSI_OPTION) $(PHP_STAN_FLAGS) $(ARGS)

phpstan-debug: #~~ runs phpstan analyze in debug mode
	$(DEBUG_PREFIX)$(PHP_STAN) analyze --configuration $(PHP_STAN_CONFIG) $(_NO_ANSI_OPTION) $(PHP_STAN_FLAGS) --debug $(ARGS)

phpstan-raw: #~~ runs phpstan analyze with raw output
	$(DEBUG_PREFIX)$(PHP_STAN) analyze --configuration $(PHP_STAN_CONFIG) $(_NO_ANSI_OPTION) $(PHP_STAN_FLAGS) --error-format=raw $(ARGS)

phpstan-list: #~~ lists all files that are processed by phpstan
	$(DEBUG_PREFIX)$(MAKE) phpstan-raw -- $(_NO_ANSI_OPTION) --debug 2>&1 | $(AWK) 'match($$0, /.+\.php$$/) { print substr($$0, RSTART, RLENGTH) }'

#--- php-cs-fixer

php-cs-fixer: #~~ runs php-cs-fixer fix
	$(DEBUG_PREFIX)$(PHP_CS_FIXER) fix --config $(PHP_CS_FIXER_CONFIG) $(_NO_ANSI_OPTION) $(PHP_CS_FIXER_FLAGS) $(ARGS)

php-cs-fixer-debug: #~~ displays used php-cs-fixer rules
	$(DEBUG_PREFIX)$(PHP_CS_FIXER) describe --config $(PHP_CS_FIXER_CONFIG) $(_NO_ANSI_OPTION) -n @ $(ARGS)

php-cs-fixer-dry-run: #~~ runs php-cs-fixer fix in dry run mode
	$(DEBUG_PREFIX)$(PHP_CS_FIXER) fix --config $(PHP_CS_FIXER_CONFIG) $(_NO_ANSI_OPTION) $(PHP_CS_FIXER_FLAGS) --dry-run $(ARGS)

php-cs-fixer-list: #~~ lists all files that are processed by php-cs-fixer
	$(DEBUG_PREFIX)$(PHP_CS_FIXER) list-files --config $(PHP_CS_FIXER_CONFIG) $(_NO_ANSI_OPTION) 2>&1 | $(AWK) '/^'\''\.\/.*\.php'\''$$/ { sub(/^'\''\.\//, ""); sub(/'\''$$/, ""); print }'

#--- rector

rector: #~~ runs rector process
	$(DEBUG_PREFIX)$(RECTOR) --config $(RECTOR_CONFIG) $(_NO_ANSI_OPTION) $(RECTOR_FLAGS) $(ARGS)

rector-debug: #~~ runs rector process in debug mode
	$(DEBUG_PREFIX)$(RECTOR) --config $(RECTOR_CONFIG) $(_NO_ANSI_OPTION) $(RECTOR_FLAGS) --debug $(ARGS)

rector-dry-run: #~~ runs rector process in dry run mode
	$(DEBUG_PREFIX)$(RECTOR) --config $(RECTOR_CONFIG) $(_NO_ANSI_OPTION) $(RECTOR_FLAGS) --dry-run $(ARGS)

rector-list: #~~ lists all files that are processed by rector
	$(DEBUG_PREFIX)$(RECTOR) --config $(RECTOR_CONFIG) $(_NO_ANSI_OPTION) --debug --dry-run | $(AWK) '/\[file\]/ { sub(".*\\[file\\] $(PWD)/", ""); if (/\.php$$/) print }'

# NOTICE: Create dummy targets for all non-target tokens
$(if $(filter $(TARGET),$(_ALL_TARGETS)) \
	,$(foreach ARG,$(sort $(filter-out $(_ALL_TARGETS),$(MAKECMDGOALS))),$(eval $(ARG):;@:) \
))

.DEFAULT:
	$(call log,Unknown command "$@".,$(COLOR_ERROR))
	$(call log \
		,Run "$(call text,make help,$(MODIFIER_UNDERLINE), \
		)" or just "$(call text,make,$(MODIFIER_UNDERLINE), \
		)" to see available commands., \
		$(COLOR_NOTICE) \
	)
	$(DEBUG_PREFIX)exit 1

.PHONY: $(_ALL_TARGETS)

#--- ansi

_ANSI_MODIFIER_NORMAL    ?= 0
_ANSI_MODIFIER_BOLD      ?= 1
_ANSI_MODIFIER_UNDERLINE ?= 4
_ANSI_MODIFIER_REVERSE   ?= 7

_ANSI_COLOR_NORMAL  ?= 0
_ANSI_COLOR_BLACK   ?= 30
_ANSI_COLOR_RED     ?= 31
_ANSI_COLOR_GREEN   ?= 32
_ANSI_COLOR_YELLOW  ?= 33
_ANSI_COLOR_BLUE    ?= 34
_ANSI_COLOR_MAGENTA ?= 35
_ANSI_COLOR_CYAN    ?= 36
_ANSI_COLOR_WHITE   ?= 37

_ANSI_COLOR_BRIGHT_NORMAL  ?= 0
_ANSI_COLOR_BRIGHT_BLACK   ?= 90
_ANSI_COLOR_BRIGHT_RED     ?= 91
_ANSI_COLOR_BRIGHT_GREEN   ?= 92
_ANSI_COLOR_BRIGHT_YELLOW  ?= 93
_ANSI_COLOR_BRIGHT_BLUE    ?= 94
_ANSI_COLOR_BRIGHT_MAGENTA ?= 95
_ANSI_COLOR_BRIGHT_CYAN    ?= 96
_ANSI_COLOR_BRIGHT_WHITE   ?= 97

_ANSI_RESET ?= $(if $(NO_ANSI),,\033[0m)

_UPPER_normal  := NORMAL
_UPPER_black   := BLACK
_UPPER_red     := RED
_UPPER_green   := GREEN
_UPPER_yellow  := YELLOW
_UPPER_blue    := BLUE
_UPPER_magenta := MAGENTA
_UPPER_cyan    := CYAN
_UPPER_white   := WHITE

#---v helpers

#**
#* Wraps a text string with ANSI color and modifier escape codes.
#* Automatically resets terminal formatting after the text.
#* Accepts either explicit color + modifiers or a combined list.
#*
#* usage:
#*   $(call text,text,color,?modifiers) | $(call text,text,color_and_or_modifiers)
#*
#* parameters:
#*   text: string
#*   color: OneOf<normal | black | red | green | yellow | blue | magenta | cyan | white>
#*   modifiers?: AnyOf<normal | bold | underline | reverse | bright>
#*
#* returns: string
#*
define text
$(call _color \
	,$(firstword $(filter $(_COLORS),$2 $3)), \
	$(strip $(filter-out $(firstword $(filter $(_COLORS),$2 $3)), \
		$2 \
		$3) \
	) \
)$1$(_ANSI_RESET)
endef

#**
#* Emits a formatted log message with <VENDOR>/<PACKAGE> prefix.
#* Applies optional color or modifier styling to the prefix.
#* Output is written through <PRINTF> with <DEBUG_PREFIX> applied.
#*
#* usage:
#*   $(call text,text,color,?modifiers) | $(call text,text,color_and_or_modifiers)
#*
#* parameters:
#* 	 message: string
#* 	 color_and_or_modifiers?: AnyOf<normal | black | red | green | yellow | blue | magenta | cyan | white | bold | underline | reverse | bright>
#*
#* returns: void
#*
define log
$(DEBUG_PREFIX)$(PRINTF) '$(call text \
	,[$(VENDOR)/$(PACKAGE)], \
	$2 \
) $1\n'
endef

#**
#* Extracts ANSI modifier codes from a list of modifier tokens.
#* Builds a semicolon-separated sequence used inside ANSI escape sequences.
#*
#* usage:
#*   $(call _get_modifiers,modifiers)
#*
#* parameters:
#*   modifiers: AnyOf<normal | bold | underline | reverse>
#*
#* returns: string
#*
define _get_modifiers
$(if \
	$(filter $(MODIFIER_NORMAL),$1),$(_ANSI_MODIFIER_NORMAL),)$(if \
	$(filter $(MODIFIER_BOLD),$1),$(_ANSI_MODIFIER_BOLD),)$(if \
	$(filter $(MODIFIER_UNDERLINE),$1),;$(_ANSI_MODIFIER_UNDERLINE),)$(if \
	$(filter $(MODIFIER_REVERSE),$1),;$(_ANSI_MODIFIER_REVERSE),)
endef

#**
#* Resolves a color token into the corresponding ANSI color code.
#* Supports bright color variants when the BRIGHT modifier is present.
#* Falls back to white if modifiers exist but color resolution fails.
#*
#* usage:
#*   $(call _get_color,color,?modifiers)
#*
#* parameters:
#*   color: OneOf<normal | black | red | green | yellow | blue | magenta | cyan | white>
#*   modifiers?: AnyOf<normal | bold | underline | reverse | bright>
#*
#* returns: _ANSI_COLOR_*
#*
define _get_color
$(if \
	$(and $(strip $2),$(or $(if $1,,1),$(filter normal,$1))),$(if \
		$(filter $(MODIFIER_BRIGHT),$2),$(_ANSI_COLOR_BRIGHT_WHITE),$(_ANSI_COLOR_WHITE)),$(if \
		$(filter $(MODIFIER_BRIGHT),$2),$(_ANSI_COLOR_BRIGHT_$(_UPPER_$1)),$(_ANSI_COLOR_$(_UPPER_$1))))
endef

#**
#* Constructs a full ANSI escape sequence using color and optional modifiers.
#* Produces the prefix used to format terminal output.
#*
#* usage:
#*   $(call _color,color,?modifiers)
#*
#* parameters:
#*   color: OneOf<normal | black | red | green | yellow | blue | magenta | cyan | white>
#*   modifiers?: AnyOf<normal | bold | underline | reverse | bright>
#*
#* returns: string
#*
define _color
$(if $(NO_ANSI),,\033[$(if $(call _get_modifiers,$2),$(call _get_modifiers,$2);)$(call _get_color,$1,$2)m)
endef

#**
#* Converts a string to title case.
#* Hyphens are replaced with spaces before capitalization.
#* Implemented through <AWK> word iteration.
#*
#* usage:
#*   $(call _to_title_case,text)
#*
#* parameters:
#*   text: string
#*
#* returns: string
#*
define _to_title_case
$(shell $(PRINTF) '%s\n' '$1' | \
	$(AWK) '{ \
		gsub(/-/, " "); \
		for (i = 1; i <= NF; i += 1 ) { \
			$$i = toupper(substr($$i, 1, 1)) \
			substr($$i, 2) \
		} \
	} \
	1' \
)
endef

#**
#* Collects argument tokens for a given target from the command-line goals list.
#* Tokens before any target (shared args) and tokens after the target (until the
#* next valid target) are both collected. The '--' separator is excluded.
#* Uses a single-pass foreach approach to avoid recursive depth limits.
#*
#* usage:
#*  $(call _collect_args,target,goals)
#*
#* parameters:
#*  target: string
#*  goals: List<string>
#*
#* returns: string
#*
define _collect_args
$(eval _ca_phase := before)$(eval _ca_shared :=)$(eval _ca_own :=)$(foreach \
	w,$(filter-out --,$2),$(if \
		$(filter before,$(_ca_phase)),$(if \
			$(filter $w,$(_ALL_TARGETS)),$(if \
				$(filter $w,$1),$(eval _ca_phase := own),$(eval _ca_phase := skip)),$(eval \
				_ca_shared := $(_ca_shared) $w)),$(if \
		$(filter own,$(_ca_phase)),$(if \
			$(filter $w,$(_ALL_TARGETS)),$(eval _ca_phase := done),$(eval \
			_ca_own := $(_ca_own) $w)),$(if \
		$(filter skip,$(_ca_phase)),$(if \
			$(filter $w,$(_ALL_TARGETS)),$(if \
				$(filter $w,$1),$(eval _ca_phase := own),))))))$(_ca_own) $(_ca_shared)
endef

#**
#* Replaces literal newline characters with escaped "\n" sequences.
#* Useful when embedding multi-line content into single-line contexts.
#*
#* usage:
#*   $(call _escape_newlines,text)
#*
#* parameters:
#*   text: string
#*
#* returns: string
#*
define _escape_newlines
$(subst $(_NEWLINE),\n,$1)
endef

define _NEWLINE


endef

#**
#* Returns the detected editor name by inspecting well-known terminal environment variables.
#*
#* usage:
#*   $(call _editor_from_env)
#*
#* returns: OneOf<vscode | phpstorm> | empty string
#*
define _editor_from_env
$(if $(filter vscode,$(TERM_PROGRAM)),$(EDITOR_VSCODE),$(if \
	$(VSCODE_PID),$(EDITOR_VSCODE),$(if \
		$(filter JetBrains-JediTerm,$(TERMINAL_EMULATOR)),$(EDITOR_PHPSTORM),)))
endef

#**
#* Returns the detected editor name based on command availability in PATH.
#*
#* usage:
#*   $(call _editor_from_command)
#*
#* returns: OneOf<vscode | phpstorm> | empty string
#*
define _editor_from_command
$(if $(shell command -v code >/dev/null 2>&1 && echo 1),$(EDITOR_VSCODE),$(if \
	$(shell { command -v pstorm || command -v phpstorm; } >/dev/null 2>&1 && echo 1),$(EDITOR_PHPSTORM),))
endef

define _HELP_AWK
function trim(string) { gsub(/^[ \t]+|[ \t]+$$/, "", string); return string }
function leaf(path) { sub(/.*\//, "", path); return path }
function strip_verbosity(text) { sub(/[ \t]*#v{1,3}([ \t]|$$)/, "", text); return text }
function line_verbosity(verbosity_match) { if (!match($$0, /#v{1,3}([[:space:]]|$$)/)) return 0; verbosity_match = substr($$0, RSTART + 1, RLENGTH - 1); sub(/[[:space:]]$$/, "", verbosity_match); return length(verbosity_match) }
function get_description() { if (!match($$0, /#~~[ ]*/)) return ""; return trim(strip_verbosity(substr($$0, RSTART + RLENGTH))) }
function derive_description(documentation, line) {
	match(documentation, /^[^\n]+/);
	line = tolower(substr(documentation, RSTART, 1)) substr(documentation, RSTART + 1, RLENGTH - 1);
	sub(/\.[[:space:]]*$$/, "", line);
	return line;
}
function push_scope(level, name, verbosity, current, parent_verbosity, parent) {
	scope_at[level] = (level > 0 && scope_at[level - 1] != "" ? scope_at[level - 1] "/" name : name);
	scope_depth = level;
	current = scope_at[level];
	parent_verbosity = (level > 0 ? scope_verbosity[scope_at[level - 1]] + 0 : 0);
	scope_verbosity[current] = (verbosity > parent_verbosity ? verbosity : parent_verbosity);
	if (current in seen_scope) return;
	seen_scope[current] = 1;
	parent = (level > 0 ? scope_at[level - 1] : "");
	if (parent == "" || parent == "main") {
		if (parent == "main" && !("main" in seen_scope)) { seen_scope["main"] = 1; top_count += 1; top_order[top_count] = "main" }
		top_count += 1;
		top_order[top_count] = current;
	} else {
		child_count[parent] += 1;
		children[parent, child_count[parent]] = current;
	}
}
function resolve_scope(symbol_type, current) {
	if (last_symbol_type != "" && last_symbol_type != symbol_type) scope_depth = 0;
	last_symbol_type = symbol_type;
	current = (scope_depth > 0 ? scope_at[scope_depth] : scope_at[0] != "" ? scope_at[0] : "main");
	if (current == "main" && !("main" in seen_scope)) { seen_scope["main"] = 1; top_count += 1; top_order[top_count] = "main" }
	return (scope_verbosity[current] + 0 > VERBOSITY + 0) ? "" : current;
}
function add(section, name, text, extra, description, line, current, idx) {
	current = resolve_scope(section);
	if (!current) return;
	idx = ++section_count[current, section];
	section_name[current, section, idx] = name;
	section_text[current, section, idx] = text;
	section_extra[current, section, idx] = extra;
	section_description[current, section, idx] = description;
	section_file[current, section, idx] = FILENAME;
	section_line[current, section, idx] = line;
	section_verbosity[current, section, idx] = line_verbosity();
}
function handle_define(documentation, description) {
	if ($$2 ~ /^_/) return;
	if ($$2 ~ /^[A-Z][A-Z0-9_]*$$/) {
		add("variables", $$2, "(macro)", (is_in_ifndef ? "?=" : ":="), get_description(), FNR);
	} else {
		description = get_description();
		if (documentation != "") add("functions", $$2, documentation, (description != "" ? description : derive_description(documentation)), "", FNR);
		else if (description != "") add("functions", $$2, "", description, "", FNR);
	}
}
function has_content(scope_path, section, idx) {
	for (idx = 1; idx <= section_count[scope_path, section] + 0; idx += 1)
		if (section_verbosity[scope_path, section, idx] + 0 <= VERBOSITY + 0) return 1;
	for (idx = 1; idx <= child_count[scope_path]; idx += 1)
		if (has_content(children[scope_path, idx], section)) return 1;
	return 0;
}
function tree_max(scope_path, depth, section, mode, best, idx, width, child_width) {
	for (idx = 1; idx <= section_count[scope_path, section] + 0; idx += 1) {
		if (section_verbosity[scope_path, section, idx] + 0 > VERBOSITY + 0) continue;
		if (mode == "value" && section_description[scope_path, section, idx] == "") continue;
		width = (mode == "value") ? length(section_text[scope_path, section, idx]) : (depth + 2) * INDENT + length(section_name[scope_path, section, idx]);
		if (width > best) best = width;
	}
	for (idx = 1; idx <= child_count[scope_path]; idx += 1) {
		child_width = tree_max(children[scope_path, idx], depth + 1, section, mode);
		if (child_width > best) best = child_width;
	}
	return best;
}
function walk(scope_path, depth, section, column, padding, width, format, idx, entry, text, extra, description, count, lines, line_idx, blank, function_printed) {
	padding = sprintf("%*s", (depth + 2) * INDENT, "");
	width = column - (depth + 2) * INDENT;
	for (idx = 1; idx <= section_count[scope_path, section] + 0; idx += 1) {
		if (section_verbosity[scope_path, section, idx] + 0 > VERBOSITY + 0) continue;
		entry = section_name[scope_path, section, idx];
		text = section_text[scope_path, section, idx];
		extra = section_extra[scope_path, section, idx];
		description = section_description[scope_path, section, idx];
		file = section_file[scope_path, section, idx];
		line = section_line[scope_path, section, idx];
		if (file != "" && file !~ /^\//) file = PWD "/" file;
		url = "";
		if (EDITOR == "vscode") {
			if (WSL_DISTRO_NAME != "") url = sprintf(EDITOR_URL_VSCODE_WSL, WSL_DISTRO_NAME, file, line);
			else url = sprintf(EDITOR_URL_VSCODE, file, line);
		} else if (EDITOR == "phpstorm") url = sprintf(EDITOR_URL_PHPSTORM, file, line);
		hyperlink = "";
		hyperlink_end = "";
		if (url != "" && !NO_ANSI) {
			hyperlink = "\033]8;;" url "\033\\";
			hyperlink_end = "\033]8;;\033\\";
		}
		entry_linked = hyperlink entry hyperlink_end;
		entry_pad = width - length(entry);
		if (entry_pad < 0) entry_pad = 0;
		entry_padded = entry_linked sprintf("%*s", entry_pad, "");
		if (section == "variables") {
			if (description != "") {
				format = sprintf("%2s  %-*s", extra, description_column, text);
				printf "%s$(call text,%s,$(COLOR_PRIMARY))  $(call text,%s (%s),$(COLOR_TERTIARY))\n", padding, entry_padded, format, description;
			} else {
				printf "%s$(call text,%s,$(COLOR_PRIMARY))  $(call text,%2s  %s,$(COLOR_TERTIARY))\n", padding, entry_padded, extra, text;
			}
		} else if (section == "commands") {
			printf "%s$(call text,%s,$(COLOR_PRIMARY))  $(call text,%s,$(COLOR_TERTIARY))\n", padding, entry_padded, text;
		} else if (section == "functions") {
			if (function_printed && VERBOSITY + 0 >= 2 && text != "") printf "\n";
			function_printed = (text != "" && VERBOSITY + 0 >= 2);
			if (extra != "" && (VERBOSITY + 0 < 2 || text == "")) {
				printf "%s$(call text,%s,$(COLOR_PRIMARY))  $(call text,%s,$(COLOR_TERTIARY))\n", padding, entry_padded, extra;
			} else {
				printf "%s$(call text,%s,$(COLOR_PRIMARY))\n", padding, entry_linked;
			}
			if (VERBOSITY + 0 >= 2 && text != "") {
				count = split(text, lines, "\n");
				blank = 0;
				for (line_idx = 1; line_idx <= count; line_idx += 1) {
					if (lines[line_idx] == "") { if (VERBOSITY + 0 < 3) break; blank = 1; continue }
					if (blank) { printf "\n"; blank = 0 }
					printf "%s$(call text,%s,$(COLOR_TERTIARY))\n", padding " ", lines[line_idx];
				}
			}
		} else {
			printf "%s$(call text,%s,$(COLOR_TERTIARY))\n", padding, text;
		}
	}
	for (idx = 1; idx <= child_count[scope_path]; idx += 1) {
		if (!has_content(children[scope_path, idx], section)) continue;
		printf "%s$(call text,%s,$(COLOR_SECONDARY))\n", padding, leaf(children[scope_path, idx]);
		walk(children[scope_path, idx], depth + 1, section, column);
	}
}
function print_section(has_previous, section, label, column, top_idx, top_path, found) {
	for (top_idx = 1; top_idx <= top_count; top_idx += 1) {
		top_path = top_order[top_idx];
		if (!has_content(top_path, section)) continue;
		if (!found++) { if (has_previous) printf "\n"; printf "$(call text,%s,$(COLOR_SECTION_LABEL))\n", label }
		if (top_path != "main") printf "%s$(call text,%s,$(COLOR_SECONDARY))\n", sprintf("%*s", INDENT, ""), leaf(top_path);
		walk(top_path, 0, section, column);
	}
	return found + 0;
}
current_file != FILENAME { current_file = FILENAME; for (i in scope_at) delete scope_at[i]; scope_depth = 0; last_symbol_type = ""; is_in_define = 0; is_in_documentation = 0; condition_depth = 0 }
/^endef([[:space:]].*)?$$/ { is_in_define = 0; next }
is_in_define { next }
/^(ifeq|ifneq)[[:space:]]/ { condition_depth += 1; next }
/^else([[:space:]]|$$)/ { next }
/^endif([[:space:]]|$$)/ { if (condition_depth > 0) { condition_depth -= 1; next } is_in_ifndef = 0; scope_depth = 0; last_symbol_type = ""; next }
condition_depth > 0 { next }
/^ifndef[[:space:]]/ { is_in_ifndef = 1; next }
/^ifdef[[:space:]]/ { scope_depth = 0; last_symbol_type = ""; next }
/^#-+/ { match($$0, /^#-+/); scope_dash_count = RLENGTH - 1; scope_suffix = substr($$0, RLENGTH + 1); match(scope_suffix, /^v*/); scope_verbosity_count = RLENGTH; push_scope(scope_dash_count - 2, trim(substr(scope_suffix, scope_verbosity_count + 1)), scope_verbosity_count); last_symbol_type = ""; next }
/^#\*\*/ { is_in_documentation = 1; documentation_text = ""; next }
is_in_documentation {
	if (/^#\*/) { sub(/^#\*[ ]?/, ""); documentation_text = documentation_text $$0 "\n"; next }
	if (/^define[ \t]+/) { sub(/\n+$$/, "", documentation_text); handle_define(documentation_text); is_in_documentation = 0; is_in_define = 1; next }
	if (!/^#/) { is_in_documentation = 0; next }
}
/^define[[:space:]]+/ { handle_define(""); is_in_define = 1; next }
/^#!!/ { sub(/^#!![ ]*/, ""); add("description", "", strip_verbosity($$0), "", "", FNR); next }
/^[A-Za-z0-9_]+[ \t]*[:?+]?=/ {
	match($$0, /^[A-Za-z0-9_]+/);
	var_name = substr($$0, RSTART, RLENGTH);
	var_rest = substr($$0, RSTART + RLENGTH);
	match(var_rest, /^[ \t]*[:?+]?=/);
	var_operator = substr(var_rest, RSTART, RLENGTH);
	gsub(/[ \t]/, "", var_operator);
	if (var_name !~ /^_/) {
		if (match($$0, /#~~[ ]*/)) {
			var_description = trim(strip_verbosity(substr($$0, RSTART + RLENGTH)));
			add("variables", var_name, trim(strip_verbosity(substr($$0, index($$0, "=") + 1, RSTART - index($$0, "=") - 1))), var_operator, var_description, FNR);
		} else {
			add("variables", var_name, trim(strip_verbosity(substr($$0, index($$0, "=") + 1))), var_operator, "", FNR);
		}
	}
	next;
}
/^[A-Za-z0-9_.-]+:([^=]|$$)/ {
	split($$0, parts, ":");
	if (parts[1] !~ /^_/ && parts[1] !~ /^[.]/ && !(parts[1] in command_seen)) {
		command_seen[parts[1]] = 1;
		add("commands", parts[1], get_description(), "", "", FNR);
	}
	next;
}
END {
	for (top_idx = 1; top_idx <= top_count; top_idx += 1) {
		top_path = top_order[top_idx];
		column = tree_max(top_path, 0, "variables", "name"); if (column > variable_column) variable_column = column;
		column = tree_max(top_path, 0, "variables", "value"); if (column > description_column) description_column = column;
		column = tree_max(top_path, 0, "functions", "name"); if (column > function_column) function_column = column;
		column = tree_max(top_path, 0, "commands", "name"); if (column > command_column) command_column = column;
	}
	has_printed = 0;
	has_printed += print_section(has_printed, "description", "Description:", 0);
	has_printed += print_section(has_printed, "variables", "Variables:", variable_column);
	has_printed += print_section(has_printed, "functions", "Functions:", function_column);
	has_printed += print_section(has_printed, "commands", "Available commands:", command_column);
}
endef

export _HELP_AWK
