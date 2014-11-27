# Installation

## Install globally

### Download

At your choice:

```bash
curl http://jolici.jolicode.com/jolici.phar -o /usr/local/bin/jolici
chmod +x /usr/local/bin/jolici
```

```bash
wget http://jolici.jolicode.com/jolici.phar -O /usr/local/bin/jolici
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


