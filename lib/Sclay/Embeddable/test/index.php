<?php
die('disabled');

ini_set('display_errors', 1);

spl_autoload_register(function ($class) {
	if (0 === strpos($class, 'Sclay_Embeddable_')) {
		$file = dirname(dirname(dirname(__DIR__))) . '/' . strtr($class, '_\\', '//') . '.php';
		is_file($file) && (require $file);
	}
});

ob_start(); ?>

<iframe src="http://fora.tv/embed?id=16054&amp;type=c" width="400" height="260" frameborder="0" scrolling="no" webkitAllowFullScreen allowFullScreen></iframe><p><a href="http://fora.tv/v/c16054">The United States and Europe: The Drags on Global Growth</a> from <a href="http://fora.tv/partner/Aspen_Institute">The Aspen Institute</a> and <a href="http://fora.tv/partner/Aspen_Institute">The Aspen Institute</a> on <a href="http://fora.tv">FORA.tv</a>

<?php
$html = ob_get_clean();

$scanner = new Sclay_Embeddable_Scanner();

$media = $scanner->scan($html);

if ($media) {
	$valid = Sclay_Embeddable_Scanner::validateMedia($media);
	$renderer = new Sclay_Embeddable_DefaultRenderer();
	$newHtml = $renderer->render($media);

} else {
	$valid = null;
	$newHtml = null;
}

header('Content-Type: text/plain; charset=UTF-8');

var_export(array(
	'media' => $media,
	'valid' => $valid,
	'newHtml' => $newHtml,
));
