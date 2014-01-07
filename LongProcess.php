<?php
/**
 * @name LongProcess.php
 * @author Au Yeung Chun Ming
 * @version 0.0.1
 * @copyright free for use
 */
class LongProcess {
	private $time_limit = 0; // warning: running process won't destroy if it runs into infinite loop
	private $kill_process = false; // true: the process will be killed when task encounter exception
	private $is_running = false;
	private $filename;
	private $tmpdir = 'tmp';
	private $root_url = '/';
	private $output;
	private $task_weight = 1;
	private $tasks = array();
	private $tasks_fail = array();
	private $tasks_message = array();
	private $tasks_progress_current = 0; // current running task index
	private $tasks_progress_weight = 0; // total weight of processed tasks
	private $tasks_total_weight = 0; // total weight of all tasks
	private $error_message = array(
		'checkKey' => 'Please assign key to the process!',
		'checkDirectoryExisted' => 'Directory is not existed!',
		'checkProcessValid' => 'Process is in progress!',
		'checkTaskValid' => 'Task is not callable!',
	);
	
	public function __construct($key = false) {
		if (!$key) 
			throw new Exception($this->error_message['checkKey']);
		$this->filename = md5($key) . '.json';
	}
	
	/**
	 * Setting config
	 */
	public function tmpdir($dir = false) {
		if ($dir !== false) {
			$this->tmpdir = $dir;
			return true;
		} else {
			return $this->tmpdir;
		}
	}
	
	public function timeLimit($sec = false) {
		if ($sec !== false) {
			if (is_int($sec)) $this->time_limit = $sec;
			return true;
		} else {
			return $this->time_limit;
		}
	}
	
	public function taskWeight($weight = false) {
		if ($weight !== false) {
			if (is_int($weight)) $this->task_weight = $weight;
			return true;
		} else {
			return $this->task_weight;
		}
	}
	
	public function rootUrl($url = false) {
		if ($url !== false) {
			$this->root_url = $url;
			return true;
		} else {
			return $this->root_url;
		}
	}
	
	public function killProcess($bool = '') {
		if ($bool !== '') {
			$this->kill_process = $bool;
			return true;
		} else {
			return $this->kill_process;
		}
	}
	
	/**
	 * Helper functions
	 */
	public function file() {
		return $this->tmpdir . '/progress-' . $this->filename;
	}
	
	public function isRunning() {
		return $this->is_running;
	}
	
	public function checkProgress() {
		$result = new stdClass();
		$result->tasks = new stdClass();
		$result->tasks->current = $this->tasks_progress_current;
		$result->tasks->total = count($this->tasks);
		$result->weight = new stdClass();
		$result->weight->current = $this->tasks_progress_weight;
		$result->weight->total = $this->tasks_total_weight;
		$result->message = $this->tasks_message;
		$result->fail = $this->tasks_fail;
		$result->running = $this->is_running;
		return $result;
	}
	
	public function output($fnc) {
		if (is_callable($fnc)) $this->output = $fnc;
	}
	
	public function taskMessage($msg) {
		$this->tasks_message[$this->tasks_progress_current] = $msg;
	}
	
	private function writeProgress() {
		file_put_contents($this->file(), json_encode($this->checkProgress()), LOCK_EX);
	}
	
	/**
	 * Task
	 */
	public function addTask($fnc, $args = array()) {
		if ($this->checkTaskValid($fnc)) {
			$this->tasks[] = array($fnc, $args, $this->task_weight);
			$this->tasks_total_weight += $this->task_weight;
		}
	}
	
	/**
	 * Process
	 */	
	private function process() {
		$this->writeProgress();	// Create progress file
		foreach ($this->tasks as $i => $task) {
			$index = $i + 1;
			list($fnc, $args, $weight) = $task;
			$this->tasks_progress_current = $index;
			//$this->writeProgress();
			try {
				call_user_func_array($fnc, $args);
			} catch (Exception $e) {
				$this->tasks_fail[$index] = $e->getMessage();
				if ($this->kill_process) {
					// end the process
					$this->afterRun();
					throw $e;
				}
			}
			$this->tasks_progress_weight += $weight;
			$this->writeProgress();
		}
	}
	
	/**
	 * Validation
	 */
	private function checkDirectoryExisted() {
		if (!file_exists($this->tmpdir))
			throw new Exception($this->error_message['checkDirectoryExisted']);
		return true;
	}
	
	private function checkProcessValid() {
		if (file_exists($this->file()))
			throw new Exception($this->error_message['checkProcessValid']);
		return true;
	}
	
	private function checkTaskValid($task) {
		if (!is_callable($task)) 
			throw new Exception($this->error_message['checkTaskValid']);
		return true;
	}
	
	private function validation() {
		$this->checkDirectoryExisted();
		$this->checkProcessValid();
	}
	
	/**
	 * Partial
	 */
	private function beforeOutput() {
		ob_end_clean();
		header("Connection: close\r\n");
		header("Content-Encoding: none\r\n");
		ignore_user_abort(true); // optional
		set_time_limit($this->time_limit);
		ob_start();
	}
	
	private function defaultOutput() {
		echo json_encode(array('progress' => $this->root_url . $this->file()));
	}
	
	private function afterOutput() {
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();     // Strange behaviour, will not work
		flush();            // Unless both are called !
		@ob_end_clean();
	}
	
	private function beforeRun() {
		$this->validation();
		$this->is_running = true;
	}
	
	private function afterRun() {
		$this->is_running = false;
		$this->writeProgress();
		sleep(3);
		unlink($this->file());
	}
	
	/**
	 * Main function
	 */
	public function run() {
		// Default argument
		$args = array(
			array(
				'folder' => $this->tmpdir,
				'name' => $this->filename,
				'path' => $this->file()
			), 
			array($this, 'writeProgress')
		);
		
		// Before run
		$this->beforeRun();
		
		// Output user will see
		$this->beforeOutput();
		if ($this->output) {
			call_user_func_array($this->output, $args);
		} else {
			$this->defaultOutput();
		}
		$this->afterOutput();
		
		// Background processing
		$this->process();
		
		// After run
		$this->afterRun();
	}
}