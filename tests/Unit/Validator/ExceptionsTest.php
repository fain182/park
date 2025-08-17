<?php

declare(strict_types=1);

namespace Park\Tests\Unit\Validator;

use Park\Analyzer\AstCodeAnalyzer;
use Park\Rule;
use Park\Scanner\FileScanner;
use Park\Validator\RuleValidator;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    private RuleValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RuleValidator();
    }

    public function testExceptWithWildcardPattern(): void
    {
        // Create a mock scenario where Domain depends on Infrastructure
        // but Legacy classes are excepted
        $mockDependencies = [
            'App\Domain\User' => ['App\Infrastructure\Database'],
            'App\Domain\Legacy\OldUser' => ['App\Infrastructure\Database'], // Should be excepted
            'App\Domain\Legacy\Migration\DataMigrator' => ['App\Infrastructure\Database'], // Should be excepted
        ];

        $rules = [
            Rule::module('App\Domain')
                ->except('App\Domain\Legacy\*')
                ->shouldNotDependOn('App\Infrastructure')
        ];

        $violations = $this->validateWithMockDependencies($rules, $mockDependencies);

        // Only App\Domain\User should violate (not the Legacy classes)
        $this->assertCount(1, $violations);
        $this->assertStringContainsString('App\Domain\User', $violations[0]);
        $this->assertStringNotContainsString('Legacy', $violations[0]);
    }

    public function testExceptWithSpecificClass(): void
    {
        $mockDependencies = [
            'App\Domain\User' => ['App\Infrastructure\Database'],
            'App\Domain\Migration\DataMigrator' => ['App\Infrastructure\Database'], // Should be excepted
        ];

        $rules = [
            Rule::module('App\Domain')
                ->except('App\Domain\Migration\DataMigrator')
                ->shouldNotDependOn('App\Infrastructure')
        ];

        $violations = $this->validateWithMockDependencies($rules, $mockDependencies);

        // Only App\Domain\User should violate
        $this->assertCount(1, $violations);
        $this->assertStringContainsString('App\Domain\User', $violations[0]);
    }

    public function testExceptWithMultiplePatterns(): void
    {
        $mockDependencies = [
            'App\Domain\User' => ['App\Infrastructure\Database'],
            'App\Domain\Legacy\OldUser' => ['App\Infrastructure\Database'], // Excepted by wildcard
            'App\Domain\Migration\DataMigrator' => ['App\Infrastructure\Database'], // Excepted by specific class
        ];

        $rules = [
            Rule::module('App\Domain')
                ->except(['App\Domain\Legacy\*', 'App\Domain\Migration\DataMigrator'])
                ->shouldNotDependOn('App\Infrastructure')
        ];

        $violations = $this->validateWithMockDependencies($rules, $mockDependencies);

        // Only App\Domain\User should violate
        $this->assertCount(1, $violations);
        $this->assertStringContainsString('App\Domain\User', $violations[0]);
    }

    public function testShouldNotBeUsedByAnyOtherModuleWithExceptions(): void
    {
        $mockDependencies = [
            'App\Service\UserService' => ['Acme\Compliance\Validator'], // Should violate
            'App\Service\OtherService' => ['Acme\Compliance\PublicApi\Gateway'], // Should be excepted
        ];

        $rules = [
            Rule::module('Acme\Compliance')
                ->except('Acme\Compliance\PublicApi\*')
                ->shouldNotBeUsedByAnyOtherModule()
        ];

        $violations = $this->validateWithMockDependencies($rules, $mockDependencies);

        // Should find violation for UserService but not for OtherService (which uses excepted PublicApi)
        $this->assertCount(1, $violations);
        $this->assertStringContainsString('UserService', $violations[0]);
    }

    public function testShouldOnlyBeUsedByWithExceptions(): void
    {
        $mockDependencies = [
            'App\Service\NotificationService' => ['App\Security\AuthService'], // Should violate
            'App\Controller\UserController' => ['App\Security\AuthService'], // Allowed
            'App\Service\UserService' => ['App\Security\PublicUtils\HashUtil'], // Should be excepted
        ];

        $rules = [
            Rule::module('App\Security')
                ->except('App\Security\PublicUtils\*')
                ->shouldOnlyBeUsedBy(['App\Controller'])
        ];

        $violations = $this->validateWithMockDependencies($rules, $mockDependencies);

        // Only NotificationService should violate (not PublicUtils which is excepted)
        $this->assertCount(1, $violations);
        $this->assertStringContainsString('NotificationService', $violations[0]);
        $this->assertStringContainsString('App\Security', $violations[0]);
    }

    public function testExceptWithNoMatches(): void
    {
        $mockDependencies = [
            'App\Domain\User' => ['App\Infrastructure\Database'],
        ];

        $rules = [
            Rule::module('App\Domain')
                ->except('App\Domain\NonExistent\*')
                ->shouldNotDependOn('App\Infrastructure')
        ];

        $violations = $this->validateWithMockDependencies($rules, $mockDependencies);

        // Should still find violation since exception doesn't match
        $this->assertCount(1, $violations);
    }

    public function testRuleBuilderSyntax(): void
    {
        // Test that the builder syntax works correctly
        $rule = Rule::module('App\Domain')
            ->except(['App\Domain\Legacy\*', 'App\Domain\Migration\DataMigrator'])
            ->shouldNotDependOn('App\Infrastructure');

        $this->assertEquals('App\Domain', $rule->getModule());
        $this->assertEquals('App\Infrastructure', $rule->getDependency());
        $this->assertEquals(['App\Domain\Legacy\*', 'App\Domain\Migration\DataMigrator'], $rule->getExceptions());
    }

    private function validateWithMockDependencies(array $rules, array $mockDependencies): array
    {
        $violations = [];
        foreach ($rules as $rule) {
            $ruleViolations = $rule->validate($mockDependencies);
            $violations = array_merge($violations, $ruleViolations);
        }

        return $violations;
    }
}