<?php
/**
 * An example usage of the CompressHTML class
 *
 * Code, example usage information, and bugs/issues can be found/reported here
 * https://github.com/bmcculley/CompressHTML
 */

require('compressHTML.class.php');

$html = file_get_contents('test.html');

$minify = new CompressHTML($html, false, true, true);

echo $minify->htmlOut();

?>