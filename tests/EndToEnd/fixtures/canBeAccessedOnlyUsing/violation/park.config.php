<?php

use Park\Rule;

return [
    Rule::module('App\Domain\User')
        ->canBeAccessedOnlyUsing([
            'App\Domain\User\User',
            'App\Domain\User\UserRepository'
        ]),
];