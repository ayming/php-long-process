# php-long-process

background process with PHP

## Usage

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
set_time_limit - http://www.php.net/manual/en/function.set-time-limit.php

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

```php
$lp->file();
```