<?php
/**
 * @name LongProcess.php
 * @author Au Yeung Chun Ming
 * @version 0.9.3
 * @copyright free for use
 * @document https://github.com/ayming/php-long-process
 */
class LongProcess {
	private $process_key;
	private $time_limit = 0; // warning: running process won't destroy if it runs into infinite loop
	private $kill_process = false; // true: the process will be killed when task encounter exception
	private $is_running = false;
	private $preceding = 'progress-';
	private $filename;
	private $file_type = 'json';
	private $folder_path = 'tmp';
	private $folder_url = 'tmp';
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
	
	public function __construct($key = '') {
		if (!empty($key)) $this->key($key);
	}
	
	/**
	 * Setting config
	 */
	public function key($key = false) {
		if ($key !== false) {
			$this->process_key = $key;
			$this->filename = md5($key) . '.' . $this->file_type;
			return true;
		} else {
			return $this->process_key;
		}
	}
	
	public function folderPath($dir = false) {
		if ($dir !== false) {
			$this->folder_path = $dir;
			return true;
		} else {
			return $this->folder_path;
		}
	}
	
	public function folderUrl($url = false) {
		if ($url !== false) {
			$this->folder_url = $url;
			return true;
		} else {
			return $this->folder_url;
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
		return $this->folder_path . '/' . $this->preceding . $this->filename;
	}
	
	public function fileUrl() {
		return $this->folder_url . '/' . $this->preceding . $this->filename;
	}
	
	public function isRunning() {
		return $this->is_running;
	}
	
	public function output($fnc, $args = array()) {
		if (is_callable($fnc)) $this->output = array($fnc, $args);
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
	
	public function taskMessage($msg) {
		if (!isset($this->tasks_message[$this->tasks_progress_current])) {
			$this->tasks_message[$this->tasks_progress_current] = array();
		}
		$this->tasks_message[$this->tasks_progress_current][] = $msg;
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
	 * Progress
	 */	
	public function checkRunningProgress() {
		// reading directory is slow
		// please use checkProgress by key instead
		$result = array();
		if ($handle = opendir($this->folder_path)) {
			while (false !== ($entry = readdir($handle))) {
				if (preg_match('/^(' . $this->preceding . ')([0-9a-f]{32})\.(' . $this->file_type . ')$/i', $entry)) {
					$result[] = $this->folder_url . '/' . $entry;
				}
			}
			closedir($handle);
		}
		return json_encode($result);
	}
	
	public function checkProgress() {
		$file = $this->file();
		if (file_exists($file)) {
			//$data = json_decode(file_get_contents($file), true);
			return $file;
		}
		return json_encode(array('key' => $this->process_key, 'running' => false));
	}
	 
	private function getProgress() {
		$result = new stdClass();
		$result->key = $this->process_key;
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
	
	private function writeProgress() {
		file_put_contents($this->file(), json_encode($this->getProgress()), LOCK_EX);
	}
	
	
	/**
	 * Validation
	 */
	private function checkKey() {
		if (!$this->process_key) 
			throw new Exception($this->error_message['checkKey']);
		return true;
	}	
	
	private function checkDirectoryExisted() {
		if (!file_exists($this->folder_path))
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
		$this->checkKey();
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
		echo json_encode(array('key' => $this->process_key, 'progress' => $this->fileUrl()));
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
		// Before run
		$this->beforeRun();
		
		// Output user will see
		$this->beforeOutput();
		if ($this->output) {
			list($fnc, $args) = $this->output;
			call_user_func_array($fnc, $args);
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