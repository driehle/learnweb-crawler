Crawler for the WWU Learnweb2
=============================

This is a crawler written in PHP to download files from the LearnWeb of the WWU and store them into a local directory. This can be e.g. a Dropbox directory, that way you'll have your lectures with you whereever you go.


Requirements
------------

  * PHP 5.3
  * Linux system (probably works under WIN, but not testet)
  

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

Copy the configuration file and edit it to reflect your needs. See below for available configuration options.

```bash
cp config.php.dist config.php
vi config.php
```

Run the update script, ideally you would do this periodically e.g. by cron:

```bash
php bin/update.php
```


Configuration
-------------

### Credentials

You need to specify the username and the passwort of your ZIV account in `username` and `password` setting in the `config.php`.

If you do not want to specify your password in the `config.php`, you may create a file `password.txt` in the same folder and use the following configuration:

```
        'username' => 'u_user01',
        'password' => trim(file_get_contents(__DIR__ . '/password.txt')),
```

This may prevent other people seeing your password while you edit the `config.php`. Keep in mind that the password needs to be stored readable for the Learnweb crawler, for it to be able to access the Learnweb.

### Courses

Add all courses you want to crawl to the `config.php`, see `config.php.dist` for some examples. There are two types of courses in Learnweb, some use the course view to list files, some use the folder view of Moodle. Looking at the URL you can easily determine which type of course you are dealing with.

### Cracking PDF files

Sometimes PDF files have a user password which you need to enter all the time you open the PDF file. Learnweb-Crawler can convert the PDF file to PS and back to PDF which generates a PDF file which can be opened without password. This only works if you know the user password and the tools `pdftops` and `ps2pdf` are installed on your system. On Debian simply install these two packages:

```bash
apt-get install ghostscript poppler-utils
```

The actually executed commands may be specified as `pdf2PsCmd` and `ps2PdfCmd` in the `config.php`, see `config.php.dist` for an example.

### Cleaning target directory

If some courses rename files or folders at will, you may end up having several files in the Dropbox twice (or even more). In that case you may want to set `"cleanTarget" => true` for those Learnweb courses, which makes learnweb crawler delete all files and folders from that directory and the downloads all files again.

To prevent a specific directory from beeing deleted, add a file named `.keep` to it.

