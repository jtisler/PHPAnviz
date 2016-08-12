## PHPAnviz
PHP library to access and control Anviz devices from web

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
$ ./anviz-server
```

### Code
Before we start, open `config.ini.example` and change gearman-server address

```sh
$ cp confing.ini.example config.ini
```
