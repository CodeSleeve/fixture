<?php

return array(
	'Diablo3' => array(
		'title'     => 'Diablo 3',
		'slug'		=> function(array $values) {
			return str_replace(' ', '-', strtolower($values['title']));
		},
		'user_id'	=> 'Travis'
	)
);

?>
