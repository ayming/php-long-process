<?php
require '../LongProcess.php';

$lp = new LongProcess();
// check input
if (empty($_POST['id'])) {
	echo $lp->checkRunningProgress();
} else {
	$lp->key($_POST['id']);
	echo $lp->checkProgress();
}
