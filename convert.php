<?php

die('disabled');

require dirname(__DIR__) . '/engine/start.php';

admin_gatekeeper();

set_time_limit(0);

elgg_load_library('elgg:videolist');

spl_autoload_register(function ($class) {
	if (0 === strpos($class, 'Sclay_Embeddable_')) {
		$file = __DIR__ . '/lib/' . strtr($class, '_\\', '//') . '.php';
		is_file($file) && (require $file);
	}
});

require __DIR__ . '/lib.php';

$subtype_id = (int) get_subtype_id('object', 'izap_videos');

$entities = elgg_get_entities(array(
	'wheres' => array("e.subtype = $subtype_id"),
	'limit' => (int) get_input('limit', 500, false),
	//'offset' => (int) get_input('offset', 0, false),
));

header('Content-Type: text/plain;charset=UTF-8');

foreach ($entities as $e) {
	/* @var \ElggObject $e */
	echo "\n\n{$e->guid}\n\n";

	echo UFCOE\analyzeObject($e) . "\n";
}