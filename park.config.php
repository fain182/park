<?php

declare(strict_types=1);

use Park\Rule;

return [
    Rule::module('Acme\Compliance')->shouldNotBeUsedByAnyOtherModule(),
    Rule::module('App\Domain')->shouldNotDependOn('App\Infrastructure'),
    Rule::module('App\Application')->shouldNotDependOn('App\Infrastructure'),
    Rule::module('App\Infrastructure')->canDependOn('App\Domain'),
    Rule::module('App\Security')->shouldOnlyBeUsedBy(['App\Controller', 'App\Middleware']),
];