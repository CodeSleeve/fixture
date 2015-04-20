<?php

return array (
	'Diablo3' => array (
		'title' => 'Diablo 3',
		'user'	=> 'Travis',
		'slug'	=> function(Game $record, array $values) {
			return str_replace(' ', '-', strtolower($record->title));
		}
	),
	
	'Skyrim' => array (
		'title' => 'Skyrim',
		'user' 	=> 'Travis',
		'slug'  =>function(Game $record, array $values) {
			return str_replace(' ', '-', strtolower($values['title']));
		}
	)
);

?>
