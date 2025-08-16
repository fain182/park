<?php

declare(strict_types=1);

use Park\Rule;

return [
    Rule::module('App\Infrastructure')->canDependOn('App\Domain'),
];