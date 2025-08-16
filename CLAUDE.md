# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Park is a PHP architecture validation tool that enforces architectural rules in PHP projects. It analyzes codebases using AST parsing to detect dependency violations and ensures modules follow defined architectural boundaries.

## Development Commands

```bash
# Run all tests
composer test
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/EndToEnd/ExceptionsTest.php
vendor/bin/phpunit tests/Unit/Analyzer/AstCodeAnalyzerTest.php

# Static analysis
composer analyse
vendor/bin/phpstan

# Combined check (tests + analysis)
composer check

# Run the tool locally
./bin/park src
./bin/park tests/EndToEnd/fixtures/shouldNotDependOn
```

## Core Architecture

### Analysis Pipeline
1. **FileScanner** discovers PHP files in target directory
2. **CodeAnalyzer** (AST-based) extracts dependencies from each file
3. **RuleValidator** validates dependency graph against configured rules
4. **ValidateCommand** formats output and returns appropriate exit codes

### Key Components

- **Rule System**: Fluent API for defining architectural constraints (`Rule::module()->shouldNotDependOn()`)
- **AST Analysis**: Uses `nikic/php-parser` to accurately detect dependencies (use statements, type hints, inheritance, etc.)
- **Exception Handling**: Supports exact class matches and wildcard patterns (`App\Legacy\*`)
- **Validation Engine**: Centralized logic in `RuleValidator` with method-specific validators

### Directory Structure
```
src/Park/
├── Application.php           # Symfony Console app setup
├── Rule.php                 # Fluent rule builder
├── Command/ValidateCommand.php
├── Analyzer/
│   ├── CodeAnalyzerInterface.php
│   ├── AstCodeAnalyzer.php   # Primary AST-based analyzer
│   └── RegexCodeAnalyzer.php # Fallback regex analyzer
├── Scanner/FileScanner.php   # File discovery
└── Validator/RuleValidator.php # Core validation logic
```

## Testing Strategy

- **End-to-End Tests**: Test complete CLI workflow with realistic fixtures
- **Unit Tests**: Focus on `AstCodeAnalyzer` demonstrating AST advantages over regex
- **Test Fixtures**: Located in `tests/EndToEnd/fixtures/` with both positive and negative cases
- Each rule type has dedicated test files with success/violation scenarios

## Configuration

Rules are defined in `park.config.php`:
```php
return [
    Rule::module('App\Domain')
        ->except('App\Domain\Legacy\*')
        ->shouldNotDependOn('App\Infrastructure'),
];
```

## Implementation Notes

### AST vs Regex Analysis
- **AstCodeAnalyzer** (preferred): Accurate, ignores comments/strings, handles complex PHP constructs
- **RegexCodeAnalyzer** (legacy): Simple but prone to false positives

### Exception Pattern Matching
The `isException()` method in `RuleValidator` handles:
- Exact matches: `App\Domain\User`
- Wildcards: `App\Domain\Legacy\*` (matches `App\Domain\Legacy` and `App\Domain\Legacy\*`)

### Dependency Detection
AST analyzer detects dependencies through:
- Use statements, type hints, return types
- Class inheritance (`extends`, `implements`)
- Exception handling, `instanceof` checks
- Static calls and class constants

## Common Tasks

### Adding New Rule Types
1. Add method to `Rule.php` builder
2. Update `RuleValidator::validateRule()` switch statement
3. Implement validation method in `RuleValidator`
4. Add end-to-end tests with fixtures

### Debugging Analysis Issues
- Check `AstCodeAnalyzer` unit tests for expected behavior
- Use end-to-end test fixtures to reproduce issues
- Compare AST vs regex analyzer results in problematic cases

### Testing Changes
Always run the full test suite before commits. The project has 26 tests with 76 assertions covering all major functionality and edge cases.