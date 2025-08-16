<?php

declare(strict_types=1);

namespace Park\Analyzer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class AstCodeAnalyzer implements CodeAnalyzerInterface
{
    public function analyzeFile(string $content): array
    {
        try {
            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $ast = $parser->parse($content);
            
            if ($ast === null) {
                return ['namespace' => null, 'dependencies' => []];
            }
            
            $visitor = new DependencyVisitor();
            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);
            
            return [
                'namespace' => $visitor->getNamespace(),
                'dependencies' => $visitor->getUsedClasses()
            ];
            
        } catch (\Exception $e) {
            return ['namespace' => null, 'dependencies' => []];
        }
    }
}

class DependencyVisitor extends NodeVisitorAbstract
{
    private ?string $namespace = null;
    private array $usedClasses = [];
    private array $useStatements = [];
    
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = $node->name ? $node->name->toString() : null;
        }
        
        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                $className = $use->name->toString();
                $alias = $use->alias ? $use->alias->toString() : $use->name->getLast();
                $this->useStatements[$alias] = $className;
                $this->usedClasses[] = $className;
            }
        }
        
        if ($node instanceof Class_ || $node instanceof Interface_) {
            if ($node->extends) {
                $this->addDependency($node->extends);
            }
            
            if ($node instanceof Class_ && $node->implements) {
                foreach ($node->implements as $interface) {
                    $this->addDependency($interface);
                }
            }
        }
        
        if ($node instanceof Node\Expr\New_ && $node->class instanceof Name) {
            $this->addDependency($node->class);
        }
        
        if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Name) {
            $this->addDependency($node->class);
        }
        
        if ($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Name) {
            $this->addDependency($node->class);
        }
        
        if ($node instanceof Node\Expr\Instanceof_ && $node->class instanceof Name) {
            $this->addDependency($node->class);
        }
        
        if ($node instanceof Node\Stmt\Catch_) {
            foreach ($node->types as $type) {
                if ($type instanceof Name) {
                    $this->addDependency($type);
                }
            }
        }
        
        if ($node instanceof Node\Param && $node->type instanceof Name) {
            $this->addDependency($node->type);
        }
        
        if ($node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod) {
            if ($node->returnType instanceof Name) {
                $this->addDependency($node->returnType);
            }
        }
    }
    
    private function addDependency(Name $name): void
    {
        $className = $name->toString();
        
        if ($name->isUnqualified() && isset($this->useStatements[$className])) {
            $className = $this->useStatements[$className];
        } elseif ($name->isQualified() || $name->isFullyQualified()) {
            $className = $name->toString();
        } else {
            return;
        }
        
        if (!in_array($className, $this->usedClasses) && str_contains($className, '\\')) {
            $this->usedClasses[] = $className;
        }
    }
    
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
    
    public function getUsedClasses(): array
    {
        return array_unique($this->usedClasses);
    }
}