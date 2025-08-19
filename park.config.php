<?php

declare(strict_types=1);

use Park\Rule;

return [
    Rule::module('Acme\Compliance')
        ->except('Acme\Compliance\PublicApi')
        ->shouldNotBeUsedByAnyOtherModule(),
        
    Rule::module('App\Domain')
        ->except(['App\Domain\Legacy', 'App\Domain\Migration\DataMigrator'])
        ->shouldNotDependOn('App\Infrastructure'),
        
    Rule::module('App\Application')->shouldNotDependOn('App\Infrastructure'),
    Rule::module('App\Infrastructure')->canDependOn('App\Domain'),
    
    Rule::module('App\Security')
        ->except('App\Security\PublicUtils')
        ->shouldOnlyBeUsedBy(['App\Controller', 'App\Middleware']),
];