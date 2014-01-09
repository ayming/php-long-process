<?php
require 'lib/Standard.php';
require '../LongProcess.php';

// check input
if (empty($_POST['id']) || empty($_POST['number'])) {
	header_status(400); die;
}

try {
	$lp = new LongProcess($_POST['id']);
	// long process
	function long_process() {
		sleep(rand(1, 2)); // simulate long process
	}
	// simulate a number of tasks
	for ($i = 0; $i < $_POST['number']; $i++) {
		$lp->addTask('long_process');
	}
	$lp->run();
} catch (Exception $e) {
	@header_status(400); 
	echo $e->getMessage();
	die;
}
?> 