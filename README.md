# Park - PHP Architecture Validation Tool

Park is a command-line tool that helps you enforce architectural rules in your PHP projects. It analyzes your codebase and ensures that modules follow the dependency rules you define, making it perfect for maintaining clean architecture in large codebases.

## Installation

Install Park via Composer:

```bash
composer require --dev your-vendor/park
```

## Quick Start

1. **Create a configuration file** `park.config.php` in your project root:

```php
<?php

use Park\Rule;

return [
    Rule::module('App\Domain')
        ->shouldNotDependOn('App\Infrastructure'),
    
    Rule::module('App\Infrastructure\Database')
        ->shouldNotBeUsedByAnyOtherModule(),
    
    Rule::module('App\Application')
        ->shouldOnlyBeUsedBy(['App\Presentation']),
];
```

2. **Run the validation**:

```bash
./vendor/bin/park validate src
```

If there are violations, Park will exit with status code 1 and display the issues:

```
[ERROR] Architecture violations found:

 - Module 'App\Domain\User' should not depend on 'App\Infrastructure\Database' (violation: shouldNotDependOn 'App\Infrastructure')
 - Module 'App\Domain\Order' should not depend on 'App\Infrastructure\EmailService' (violation: shouldNotDependOn 'App\Infrastructure')
```

## Available Rules

### `shouldNotDependOn(string $module)`

Prevents a module from depending on another module:

```php
Rule::module('App\Domain')
    ->shouldNotDependOn('App\Infrastructure')
```

### `shouldNotBeUsedByAnyOtherModule()`

Makes a module internal - no other modules can use it:

```php
Rule::module('App\Infrastructure\Database\Connection')
    ->shouldNotBeUsedByAnyOtherModule()
```

### `shouldOnlyBeUsedBy(array $modules)`

Restricts which modules can use this module:

```php
Rule::module('App\Application')
    ->shouldOnlyBeUsedBy(['App\Presentation\Web', 'App\Presentation\Api'])
```

## Exceptions

Sometimes you need to allow specific classes to break the rules. Use the `except()` method:

```php
Rule::module('App\Domain')
    ->except('App\Domain\Legacy')  // All Legacy namespace classes
    ->shouldNotDependOn('App\Infrastructure')
```

You can specify exact classes or namespace patterns:

```php
Rule::module('App\Domain')
    ->except([
        'App\Domain\Legacy\OldUser',     // Exact class
        'App\Domain\Migration'           // All migration classes
    ])
    ->shouldNotDependOn('App\Infrastructure')
```

## Usage

### Basic usage

```bash
./vendor/bin/park validate        # Analyzes 'src' directory
./vendor/bin/park validate app    # Analyzes 'app' directory
./vendor/bin/park validate src/MyModule  # Analyzes specific directory
```

### Gradual Enforcement (Diff Mode)

For existing projects with legacy violations, use the `diff` command to only block **new** violations:

```bash
./vendor/bin/park diff src        # Compare with main branch
./vendor/bin/park diff --base=develop  # Compare with develop branch
```

The diff command:
1. Analyzes violations in your current branch
2. Switches to the base branch and analyzes violations there
3. Shows only the **differences** between branches
4. Returns success if no new violations, failure if new violations found

**Example output:**
```
📊 Violation Diff vs main branch

➕ New violations (2):
  src/Domain/User.php → App\Domain\User depends on App\Infrastructure\Database
  src/Service/Email.php → App\Service\Email depends on App\Infrastructure\Queue

➖ Fixed violations (1):
  src/Domain/Order.php → App\Domain\Order depends on App\Infrastructure\Logger

⚠️  CI Status: FAILED (new violations detected)
```

This approach allows you to:
- **Introduce Park gradually** on existing codebases
- **Block new violations** without requiring immediate fixes for existing ones
- **Track progress** by seeing fixed violations over time

### In CI/CD

Park is designed to run in CI environments. Add it to your pipeline:

**Full validation** (fails on any violation):
```yaml
# GitHub Actions example
- name: Validate Architecture
  run: ./vendor/bin/park validate src
```

**Gradual enforcement** (fails only on new violations):
```yaml
# GitHub Actions example - perfect for existing projects
- name: Check Architecture Diff
  run: ./vendor/bin/park diff src --base=main
```

```yaml
# GitLab CI example
validate_architecture:
  script:
    - ./vendor/bin/park diff src --base=main
```

If violations are found, Park exits with code 1, failing the build.

## Configuration Examples

### Clean Architecture

```php
<?php

use Park\Rule;

return [
    // Domain layer should not depend on infrastructure
    Rule::module('App\Domain')
        ->shouldNotDependOn('App\Infrastructure'),
    
    // Domain should not depend on application layer
    Rule::module('App\Domain')
        ->shouldNotDependOn('App\Application'),
    
    // Infrastructure should not depend on presentation
    Rule::module('App\Infrastructure')
        ->shouldNotDependOn('App\Presentation'),
    
    // Keep database access isolated
    Rule::module('App\Infrastructure\Database')
        ->shouldOnlyBeUsedBy(['App\Infrastructure\Repository']),
];
```

### Hexagonal Architecture

```php
<?php

use Park\Rule;

return [
    // Core business logic isolation
    Rule::module('App\Core')
        ->shouldNotDependOn('App\Adapter'),
    
    // Ports should only be used by adapters
    Rule::module('App\Port')
        ->shouldOnlyBeUsedBy(['App\Adapter', 'App\Core']),
    
    // External dependencies isolation
    Rule::module('App\Adapter\Database')
        ->shouldNotBeUsedByAnyOtherModule(),
];
```

### Microservice Boundaries

```php
<?php

use Park\Rule;

return [
    // User service isolation
    Rule::module('App\User')
        ->shouldNotDependOn('App\Order'),
    
    Rule::module('App\User')
        ->shouldNotDependOn('App\Payment'),
    
    // Order service isolation  
    Rule::module('App\Order')
        ->shouldNotDependOn('App\User'),
    
    // Shared kernel can be used by all
    Rule::module('App\Shared')
        ->shouldOnlyBeUsedBy(['App\User', 'App\Order', 'App\Payment']),
];
```

## What Park Analyzes

Park uses PHP's Abstract Syntax Tree (AST) to accurately detect dependencies through:

- Class imports (`use` statements)
- Type hints in method parameters
- Return type declarations
- Class inheritance (`extends`, `implements`)
- Exception catching (`catch` blocks)
- `instanceof` checks

It **ignores** dependencies mentioned only in:
- Comments
- String literals
- Method bodies (variable assignments, method calls)

## Requirements

- PHP 8.1 or higher
- Composer

## Tips

1. **Start simple**: Begin with a few basic rules and add more as your architecture evolves
2. **Use exceptions sparingly**: Too many exceptions might indicate architectural problems
3. **Run early**: Include Park in your CI pipeline to catch violations early
4. **Document your rules**: Add comments to your `park.config.php` explaining the architectural decisions

## Exit Codes

- `0`: No violations found
- `1`: Architecture violations detected
- `2`: Configuration or runtime error

## License

MIT