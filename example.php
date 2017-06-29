<?php
/**
 * Example File
 *
 * This is a simple example that allows you to test the service before installing.
 * Normally you would be able to add this program via Composer so your testing
 * procedure may differ.
 */

require "vendor/autoload.php";

use ImageCompress\Compress;

try {

	$testC = new Compress();
	$compressionResults = $testC->doImageCompression( "http://27ldk4j1esh2tto962ds2vk1.wpengine.netdna-cdn.com/wp-content/uploads/dictionary-examples-1200x330.jpg", FALSE);
	echo $compressionResults;

} catch(\Exception $e) {
	echo $e;
}