# php-long-process

background process with PHP

## Usage

### Define

```php
$lp = new LongProcess($processKey);
```

### Setting

#### Process Key

Process key is important to identify process

```php
$lp->key($processKey); // set
$lp->key(); // get
```

#### Temporary Directory

A directory stores the progress files (Default: 'tmp')

```php
$lp->tmpdir($tmpdir); // set
$lp->tmpdir(); // get
```

#### Time Limit

Limits the maximum execution time (Default: 0)

[set_time_limit](http://www.php.net/manual/en/function.set-time-limit.php)

```php
$lp->timeLimit($timeLimit); // set
$lp->timeLimit(); // get
```

#### Task Weight

Used to monitor the progress (Default: 1)

```php
$lp->taskWeight($taskWeight); // set
$lp->taskWeight(); // get
```

#### Root Url

Path before tmpdir (Default: '')

```php
$lp->rootUrl($rootUrl); // set
$lp->rootUrl(); // get
```

#### killProcess

If true, process will be killed when task encounter exception (Default: false)

```php
$lp->killProcess($bool); // set
$lp->killProcess(); // get
```

### Helper functions

#### File

Path of progress file

```php
$lp->file();
```

#### File Url

Url of progress file

```php
$lp->fileUrl();
```

#### Is Running

Check the running status of process

```php
$lp->isRunning();
```

#### Output

While the background process started, any output would not be displayed. The output callback is executed before the background process. It can output any information. (Default: '{"key":(**processKey**),"progress":(**fileUrl**)}')

```php
$lp->output($callback, $arguments);
```

### Task

#### addTask

Add new task to the background process

```php
$lp->addTask($callback, $arguments);
```

#### Task Message

Add message to progress file within a task

```php
$lp->taskMessage($message);
```

### Progress

#### Check Running Progress

Get array of progress files

```php
$lp->checkRunningProgress();
```

#### Check Progress

Get progress by process key

```php
$lp->checkProgress($processKey);
```

### Excute

Start running the process

```php
$lp->run();
```

## Minimum Example

```php
$lp = new LongProcess('1');
function task() {
	sleep(1); // simulate long process
}
$lp->addTask('task');
$lp->run();
```

## License

#### The MIT License (MIT)

Copyright (c) 2014 php-long-process

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Release History

 * 2014-01-10   v0.9.2   taskMessage can be called multiple times.
 * 2014-01-09   v0.9.1   First release.