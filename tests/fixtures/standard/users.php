<?php

return array (
    'Travis' => array (
        'first_name' => function ($record) {
            if ('Bennett' === $record['last_name']) {
                return 'Travis';
            }
            return null;
        },
        'last_name' => 'Bennett'
    )
);
