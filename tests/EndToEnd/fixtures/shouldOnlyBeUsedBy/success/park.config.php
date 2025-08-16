<?php

declare(strict_types=1);

use Park\Rule;

return [
    Rule::module('App\Security')->shouldOnlyBeUsedBy(['App\Controller']),
];