<?php

return [
    'Diablo3' => [
        'title' => 'Diablo 3',
        'user'    => 'Travis',
        'slug'    => function (Game $record, array $values) {
            return str_replace(' ', '-', strtolower($record->title));
        }
    ],
    
    'Skyrim' => [
        'title' => 'Skyrim',
        'user'    => 'Travis',
        'slug'  =>function (Game $record, array $values) {
            return str_replace(' ', '-', strtolower($values['title']));
        }
    ]
];
