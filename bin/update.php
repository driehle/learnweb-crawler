<?php

// detect base dir
$baseDir = dirname(dirname(__FILE__));
chdir ($baseDir);

// enable autoloading
require ($baseDir . '/vendor/autoload.php');

// load config file
if (!file_exists($baseDir . '/config.php')) {
	echo "Please copy config.php.dist to config.php and edit it for your needs.\n";
	exit;
}
$config = new Zend\Config\Config(include $baseDir . '/config.php');

// set debugging flag
define('LEARNWEB_DEBUG', (bool) $config->debug);

// create pdf cracker
$pdf2Ps = (isset($config->pdf2PsCmd) ? $config->pdf2PsCmd : null);
$ps2Pdf = (isset($config->ps2Pdf) ? $config->ps2Pdf : null);
$pdf = new LearnwebCrawler\PdfCrack($pdf2Ps, $ps2Pdf);

// handle learnweb courses
if (isset($config->learnwebcourses)) {
	if (LEARNWEB_DEBUG) {
		echo "Going to crawl the learnweb courses...\n";
	}
	
	$inst = new LearnwebCrawler\Resource\LearnwebCourse();
	$inst->setAuth($config->username, $config->password);
	$inst->setPdfCrack($pdf);
	$inst->setDropboxPath($config->dropbox_path);
	
	foreach ($config->learnwebcourses as $action) {
		$inst->handle ($action);
	}
	
	unset($inst);
}

// handle learnweb folders
if (isset($config->learnwebfolders)) {
	if (LEARNWEB_DEBUG) {
		echo "Going to crawl the learnweb folders...\n";
	}
	
	$inst = new LearnwebCrawler\Resource\LearnwebFolder();
	$inst->setAuth($config->username, $config->password);
	$inst->setPdfCrack($pdf);
	$inst->setDropboxPath($config->dropbox_path);
	
	foreach ($config->learnwebfolders as $action) {
		$inst->handle ($action);
	}
	
	unset($inst);
}

// handle udoo
if (isset($config->udoo)) {
	if (LEARNWEB_DEBUG) {
		echo "Going to crawl uDoo...\n";
	}
	
	$inst = new LearnwebCrawler\Resource\Udoo();
	$inst->setAuth($config->username, $config->password);
	$inst->setPdfCrack($pdf);
	$inst->setDropboxPath($config->dropbox_path);
	
	foreach ($config->udoo as $action) {
		$inst->handle ($action);
	}
	
	unset($inst);
}
