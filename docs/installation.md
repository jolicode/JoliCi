# Installation

## Install globally

### Download

[Download the last version of jolici](https://github.com/jolicode/JoliCi/releases), i.e. for v0.3.1:

```bash
curl https://github.com/jolicode/JoliCi/releases/download/v0.3.1/jolici.phar -o /usr/local/bin/jolici
chmod +x /usr/local/bin/jolici
```

```bash
wget https://github.com/jolicode/JoliCi/releases/download/v0.3.1/jolici.phar -O /usr/local/bin/jolici
chmod +x /usr/local/bin/jolici
```

### Composer

```
composer global require "jolicode/jolici:*"
```

## Install per project

### Download

```
curl http://jolici.jolicode.com/jolici.phar
```

```
wget http://jolici.jolicode.com/jolici.phar
```

### Composer

```
composer require --dev "jolicode/jolici:*"
```

This tool is mainly used for development purpose so here we set the --dev option, if this tool is not only for developers remove that option.


