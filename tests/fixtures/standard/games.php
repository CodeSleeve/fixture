<?php

return [
    'Diablo3' => [
        'title'     => 'Diablo 3',
        'slug'        => function (array $values) {
            return str_replace(' ', '-', strtolower($values['title']));
        },
        'user_id'    => 'Travis'
    ]
];
