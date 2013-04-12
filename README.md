Crawler for the WWU Learnweb2
=============================

This is a crawler writtein PHP to download files from the LearnWeb of the WWU and 
store them into a local directory. This can be e.g. a Dropbox directory, that way
you'll have your lectures with you whereever you go.


Requirements
------------

  * PHP 5.3
  * Linux system (probably works unter WIN, but not testet)
  

Installation
------------

Clone the repository:

```bash
git clone https://github.com/driehle/learnweb-crawler.git
cd learnweb-crawler
```

Check for an update of [Composer](http://getcomposer.org/) and install dependencies:

```bash
php composer.phar self-update
php composer.phar install
```

Copy the configuration file and edit it to reflect your needs:

```bash
cp config.php.dist config.php
vi config.php
```

Run the update script, ideally you would do this periodically e.g. by cron:

```
php bin/update.php
```
