<?php

declare(strict_types=1);

namespace Park\Tests\Unit\Analyzer;

use Park\Analyzer\AstCodeAnalyzer;
use PHPUnit\Framework\TestCase;

class AstCodeAnalyzerTest extends TestCase
{
    private AstCodeAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new AstCodeAnalyzer();
    }

    public function testBasicUseStatements(): void
    {
        $code = '<?php
namespace App\Service;

use App\Domain\User;
use App\Repository\UserRepository;

class UserService
{
    private UserRepository $repo;
    
    public function getUser(): User
    {
        return new User();
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        $this->assertEquals('App\Service', $result['namespace']);
        $this->assertContains('App\Domain\User', $result['dependencies']);
        $this->assertContains('App\Repository\UserRepository', $result['dependencies']);
    }

    public function testIgnoresCommentsAndStrings(): void
    {
        $code = '<?php
namespace App\Service;

use App\Domain\User;

class UserService
{
    public function test(): void
    {
        // This should be ignored: use App\Fake\Comment;
        /* Another comment with use App\Fake\MultilineComment; */
        $string = "use App\Fake\StringContent;";
        
        echo "Hello World";
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        // AST should ignore comments and strings
        $this->assertContains('App\Domain\User', $result['dependencies']);
        $this->assertNotContains('App\Fake\Comment', $result['dependencies']);
        $this->assertNotContains('App\Fake\MultilineComment', $result['dependencies']);
        $this->assertNotContains('App\Fake\StringContent', $result['dependencies']);
    }

    public function testTypeHintsAndReturnTypes(): void
    {
        $code = '<?php
namespace App\Service;

use App\Domain\User;
use App\ValueObject\Email;
use App\Exception\UserNotFoundException;

class UserService
{
    public function findUser(Email $email): User
    {
        // Type hint: Email, Return type: User
        return new User();
    }
    
    public function processUsers(array $users): void
    {
        // Only custom classes should be detected
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        $this->assertContains('App\Domain\User', $result['dependencies']);
        $this->assertContains('App\ValueObject\Email', $result['dependencies']);
        $this->assertContains('App\Exception\UserNotFoundException', $result['dependencies']);
    }

    public function testExtendsAndImplements(): void
    {
        $code = '<?php
namespace App\Service;

use App\Contract\ServiceInterface;
use App\Base\AbstractService;
use App\Trait\LoggableTrait;

class UserService extends AbstractService implements ServiceInterface
{
    use LoggableTrait;
}';

        $result = $this->analyzer->analyzeFile($code);
        
        $this->assertContains('App\Contract\ServiceInterface', $result['dependencies']);
        $this->assertContains('App\Base\AbstractService', $result['dependencies']);
        $this->assertContains('App\Trait\LoggableTrait', $result['dependencies']);
    }

    public function testCatchAndInstanceof(): void
    {
        $code = '<?php
namespace App\Service;

use App\Exception\UserNotFoundException;
use App\Exception\ValidationException;
use App\Domain\User;

class UserService
{
    public function processUser($data): void
    {
        try {
            if ($data instanceof User) {
                // Process user
            }
        } catch (UserNotFoundException | ValidationException $e) {
            // Handle exceptions
        }
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        $this->assertContains('App\Exception\UserNotFoundException', $result['dependencies']);
        $this->assertContains('App\Exception\ValidationException', $result['dependencies']);
        $this->assertContains('App\Domain\User', $result['dependencies']);
    }

    public function testStaticCallsAndClassConstants(): void
    {
        $code = '<?php
namespace App\Service;

use App\Util\StringHelper;
use App\Config\AppConfig;

class UserService
{
    public function formatName(string $name): string
    {
        $maxLength = AppConfig::MAX_NAME_LENGTH;
        return StringHelper::capitalize($name);
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        $this->assertContains('App\Util\StringHelper', $result['dependencies']);
        $this->assertContains('App\Config\AppConfig', $result['dependencies']);
    }

    public function testComplexScenarioWithCommentsAndStrings(): void
    {
        $code = '<?php
namespace App\Service;

use App\Domain\User;
use App\Repository\UserRepository;

/**
 * This comment contains fake dependencies:
 * use App\Fake\CommentDependency;
 * new App\Fake\NewInComment();
 */
class UserService extends /* use App\Fake\InlineComment; */ UserRepository
{
    public function processUser(): User
    {
        $fakeCode = "use App\Fake\StringDependency; new App\Fake\StringNew();";
        
        try {
            return new User();
        } catch (App\Exception\UserException $e) {
            // Handle exception
        }
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        // AST correctly identifies real dependencies
        $this->assertContains('App\Domain\User', $result['dependencies']);
        $this->assertContains('App\Repository\UserRepository', $result['dependencies']);
        
        // AST ignores fake dependencies in comments and strings
        $this->assertNotContains('App\Fake\CommentDependency', $result['dependencies']);
        $this->assertNotContains('App\Fake\NewInComment', $result['dependencies']);
        $this->assertNotContains('App\Fake\StringDependency', $result['dependencies']);
        $this->assertNotContains('App\Fake\StringNew', $result['dependencies']);
    }

    public function testHandlesInvalidPhp(): void
    {
        $invalidCode = '<?php
        This is not valid PHP syntax {{{
        namespace App\Invalid;
        use App\Something;
        ';

        $result = $this->analyzer->analyzeFile($invalidCode);
        
        // Should handle gracefully
        $this->assertNull($result['namespace']);
        $this->assertEmpty($result['dependencies']);
    }

    public function testNewInstancesWithComplexExpressions(): void
    {
        $code = '<?php
namespace App\Service;

use App\Factory\UserFactory;
use App\Domain\User;

class UserService
{
    public function createUser(): User
    {
        return (new UserFactory())->create();
    }
}';

        $result = $this->analyzer->analyzeFile($code);
        
        $this->assertContains('App\Factory\UserFactory', $result['dependencies']);
        $this->assertContains('App\Domain\User', $result['dependencies']);
    }
}