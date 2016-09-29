## PHPAnviz
PHP library to access and control Anviz devices

### Version
0.9.0

### Installation
```sh
$ apt-get install gearman-job-server php5-gearman libgearman-dev
```

After the installation is done run gearman job server
```sh
$ gearmand -d
```

Set server permissions
```sh
$ chmod +x anviz-server
```

I'm recommending you to get Supervisor and run server with it or start the server mannually:
```sh
$ ./anviz-server_xYour_arch
```

You can download source of anviz-server <a href="https://github.com/jtisler/anviz">here</a>

### Code & Functions
Before we start, open `config.ini.example` and change gearman-server address, save file and create `config.ini`

```sh
$ cp confing.ini.example config.ini
```

#### Get DateTime

```php
<?php

require "PHPAnviz.php";
//third parameter is optional, if not set class uses default config.ini we've created earlier
$anviz = new PHPAnviz(1, 5010, "/path/to/optional/config/file.ini");

//format is optional, by default method getDateTime returns datetime in Y-m-d H:i:s format
echo $anviz->getDateTime("Y/m/d H:i:s");
```

#### Set DateTime

```php
//parameter datetime is optional, if not set method will send current timestamp to device
$result = $anviz->setDateTime("2016-08-12 22:00:00"); //true if successful, false if failed
```

#### Get the firmware version, communication password, sleep time, volume, language, date and time format, attendance state, language setting flag, command version

```php
$result = $anviz->getInfo1(); //array
```

#### Set the communication password, sleep time, volume, language, date format, attendance state, and language setting flag.

```php
//returns true if successfull or false if failed
//pass 0xFF if you don't want to update paramete
$result = $anviz->setInfo1("12345", 10, 4, 1, 12, 0xFF, 0xFF); //true if success, false if failed
```

#### Get the T&A device Compare Precision, Fixed Wiegand Head Code, Wiegand Option, Work code permission, real-time mode setting, FP auto update setting, relay mode, Lock delay, Memory full alarm, Repeat attendance delay, door sensor delay, scheduled bell delay

```php
$result = $anviz->getInfo2(); //array
```

#### Get the IP address, subnet Mask, MAC address, Default gateway, Server IP address,Far limit, Com port NO., TCP/IP mode, DHCP limit.

```php
$result = $anviz->getTCPIPParameters(); //array
```

#### Get record information, including the amount of Used User, Used FP, Used Password, Used Card, All Attendance Record, and New Record.

```php
$result = $anviz->getRecordInformation(); //array
```

#### Download Time attendance records

```php
$result = $anviz->downloadTARecords(PHPAnviz::DOWNLOAD_NEW); //array of records
```
If you want to download all records pass `PHPAnviz::DOWNLOAD_ALL` or if you want to download new records only pass `PHPAnviz::DOWNLOAD_NEW`

#### Download staff information (users)

```php
$result = $anviz->downloadStaffInfo(); //array of users
```

#### Upload staff information (users)

```php
$users = array(
  0 => array(
    'user_id' => 1,
    'pwd' => '32015',
    'card_id' => '77421231',
    'name' => 'Test user 1',
    'department' => 0xFF,
    'group' => 1,
    'attendance_mode' => 0xFF,
    'pwd_8_digit' => 0xFF,
    'keep' => 0,
    'special_info' => 0xFF
  ),
  .
  .
  .
  n => array(
    'user_id' => n,
    'pwd' => '32235',
    'card_id' => '23521231',
    'name' => 'Test user n',
    'department' => 0xFF,
    'group' => 1,
    'attendance_mode' => 0xFF,
    'pwd_8_digit' => 0xFF,
    'keep' => 0,
    'special_info' => 0xFF
  ),
);

$anviz->uploadStaffInfo($users); //true if successful, false if failed
```

#### Download Fingerprint template

```php
//first parameter is user id, second parameter is finger print (1 for FP1, 2 for FP2)
$template = $anviz->downloadFPTemplate(1, 1); //string
```

#### Get device id

```php
$id = $anviz->getDeviceId(); //int
```

#### Set device id

```php
$result = $anviz->setDeviceId(13); //true if successful, false if failed
```

#### Clear ALL users and their data

```php
$result = $anviz->clearUsers(); //true if  successful, false if failed
```

#### Clear Time Attendance records

```php
$result = $anviz->clearRecords(PHPAnviz::CLEAR_NEW_PARTIALY, 24); //true if successful, false if failed
```

`PHPAnviz::CLEAR_ALL` -> if you want to delete all records

`PHPAnviz::CLEAR_NEW` -> remove all "new record" signs

`PHPAnviz::CLEAR_NEW_PARTIALY`, `int $n` -> remove first `$n` "new records" signs

#### Force T&A device output signal to open door

```php
$result = $anviz->openDoor(); //true if successful, false if failed
```

#### Get Attendance state table
```php
$result = $anviz->getAttendanceStateTable(); //returns array of states (MAX 16)
```

#### Set Attendance state table
```php

//MAX 16 elements
$states = array('IN', 'OUT', 'BREAK');

$result = $anviz->setTAStateTable($states); //true if successful, false if failed
