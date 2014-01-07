<?php
require 'LongProcess.php';

// check input
if (empty($_POST['id']) || empty($_POST['number'])) {
	header_status(400); die;
}

$cacheFolder = $_SERVER['DOCUMENT_ROOT'] . '/cache/';

try {
	$lp = new LongProcess($_POST['id']);
	$lp->tmpdir('cache');
	//$lp->killProcess(true);
	// long process
	function long_process($a, $b, $c) {
		global $lp;
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cache/long_process.txt', print_r(array($a, $b, $c->checkProgress(), $lp->file()), true));
		//if ($b == 3) throw new Exception('Some error here.');
		//if ($b == 6) throw new Exception('Some error here.');
		$lp->taskMessage('YO! I am task ' . ($b+1));
		sleep(1);
	}
	//$lp->process('long_process');
	for ($i = 0; $i < $_POST['number']; $i++) {
		$lp->addTask('long_process', array($cacheFolder, $i, $lp));
	}
	$lp->run();
} catch (Exception $e) {
	header_status(400); 
	echo $e->getMessage();
	die;
}


// function
function header_status($statusCode) {
	static $status_codes = null;

	if ($status_codes === null) {
		$status_codes = array (
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended'
		);
	}

	if ($status_codes[$statusCode] !== null) {
		$status_string = $statusCode . ' ' . $status_codes[$statusCode];
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
	}
}
?> 