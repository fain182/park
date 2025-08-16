<?php

declare(strict_types=1);

use Park\Rule;

return [
    Rule::module('App\Domain')->shouldNotDependOn('App\Infrastructure'),
];