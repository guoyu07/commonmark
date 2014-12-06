An extensible Markdown parser for PHP
=====================================

<p align="center">
<a href="https://github.com/fluxbb/markdown/releases"><img src="https://img.shields.io/github/release/fluxbb/markdown.svg?style=flat-square" alt="Latest Stable Version"></img></a>
<a href="https://travis-ci.org/fluxbb/markdown"><img src="https://img.shields.io/travis/fluxbb/markdown/master.svg?style=flat-square" alt="Build Status"></img></a>
<a href="LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></img></a>
</p>

An object oriented, fully extensible markdown parser for PHP 5.4 and above.
It contains a collection of extensions, making it easy to replace, add or remove parsing mechanics.

[**Try Demo**][ciconia-demo] / [**Docs**][ciconia-docs] / [**Supported Syntax**][ciconia-syntax] / [**API Reference**][ciconia-api]

*   Based on John Gruber's Markdown.pl
*   Originally started by [Kazuyuki Hayashi](https://github.com/kzykhys)

*   [Github Flavored Markdown](https://help.github.com/articles/github-flavored-markdown) support (disabled by default)

    * Multiple underscores in words
    * New lines
    * Fenced code blocks
    * Task lists
    * Table
    * URL Autolinking

*   Tested with [karlcow/markdown-testsuite](https://github.com/karlcow/markdown-testsuite)

## Requirements

* PHP 5.4+
* Composer

## Installation

Add the library to your Composer dependencies:

    composer require fluxbb/markdown
    
Next, use Composer to install the library and its dependencies:

    composer install

## Usage

### Traditional Markdown

``` php
use Ciconia\Ciconia;

$ciconia = new Ciconia();
$html = $ciconia->render('Markdown is **awesome**');

// <p>Markdown is <em>awesome</em></p>
```

### Github Flavored Markdown

To activate 6 gfm features:

``` php
use Ciconia\Ciconia;
use Ciconia\Extension\Gfm;

$ciconia = new Ciconia();
$ciconia->addExtension(new Gfm\FencedCodeBlockExtension());
$ciconia->addExtension(new Gfm\TaskListExtension());
$ciconia->addExtension(new Gfm\InlineStyleExtension());
$ciconia->addExtension(new Gfm\WhiteSpaceExtension());
$ciconia->addExtension(new Gfm\TableExtension());
$ciconia->addExtension(new Gfm\UrlAutoLinkExtension());

$html = $ciconia->render('Markdown is **awesome**');

// <p>Markdown is <em>awesome</em></p>
```

### Options

Option             | Type    | Default | Description                   |
-------------------|---------|---------|-------------------------------|
**tabWidth**       | integer | 4       | Number of spaces              |
**nestedTagLevel** | integer | 3       | Max depth of nested HTML tags |
**strict**         | boolean | false   | Throws exception if markdown contains syntax error |

``` php
use Ciconia\Ciconia;

$ciconia = new Ciconia();
$html = $ciconia->render(
    'Markdown is **awesome**',
    ['tabWidth' => 8, 'nestedTagLevel' => 5, 'strict' => true]
);
```

Rendering HTML or XHTML
-----------------------

Ciconia renders HTML by default. If you prefer XHTML:

``` php
use Ciconia\Ciconia;
use Ciconia\Renderer\XhtmlRenderer;

$ciconia = new Ciconia(new XhtmlRenderer());
$html = $ciconia->render('Markdown is **awesome**');

// <p>Markdown is <em>awesome</em></p>
```

## Extensions

### How to Extend

Creating extension is easy, just implement `Ciconia\Extension\ExtensionInterface`.

Your class must implement 2 methods.

#### _void_ register(`Ciconia\Markdown` $markdown)

Register your callback to markdown event manager.
`Ciconia\Markdown` is instance of `Ciconia\Event\EmitterInterface` (looks like Node.js's EventEmitter)

#### _string_ getName()

Returns the name of your extension.
If your name is the same as one of core extension, it will be replaced by your extension.

### Extension Example

This sample extension turns `@username ` mentions into links.

``` php
<?php

use Ciconia\Common\Text;
use Ciconia\Extension\ExtensionInterface;

class MentionExtension implements ExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(\Ciconia\Markdown $markdown)
    {
        $markdown->on('inline', [$this, 'processMentions']);
    }

    /**
     * @param Text $text
     */
    public function processMentions(Text $text)
    {
        // Turn @username into [@username](http://example.com/user/username)
        $text->replace('/(?:^|[^a-zA-Z0-9.])@([A-Za-z]+[A-Za-z0-9]+)/', function (Text $w, Text $username) {
            return '[@' . $username . '](http://example.com/user/' . $username . ')';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mention';
    }
}
```

Register your extension.

``` php
<?php

require __DIR__ . '/vendor/autoload.php';

$ciconia = new \Ciconia\Ciconia();
$ciconia->addExtension(new MentionExtension());
echo $ciconia->render('@kzykhys my email address is example@example.com!');
```

Output

``` html
<p><a href="http://example.com/user/kzykhys">@kzykhys</a> my email address is example@example.com!</p>
```

Each extension handles string as a `Text` object. See [API section of kzykhys/Text][textapi].

### Events

Possible events are:

| Event      | Description                                                                               |
|------------|-------------------------------------------------------------------------------------------|
| initialize | Document level parsing. Called at the first of the sequence.                              |
| block      | Block level parsing. Called after `initialize`                                            |
| inline     | Inline level parsing. Generally called by block level parsers.                            |
| detab      | Convert tabs to spaces. Generally called by block level parsers.                          |
| outdent    | Remove one level of line-leading tabs or spaces. Generally called by block level parsers. |
| finalize   | Called after `block`                                                                      |

[See the source code of Extensions](src/Ciconia/Extension)

[See events and timing information](https://gist.github.com/kzykhys/7443440)

### Create your own Renderer

Ciconia supports HTML/XHTML output. but if you prefer customizing the output,
just create a class that implements `Ciconia\Renderer\RendererInterface`.

See [Ciconia\Renderer\RendererInterface](src/Ciconia/Renderer/RendererInterface.php)

## Command Line Interface

### Usage

Basic Usage: (Outputs result to STDOUT)

    ciconia /path/to/file.md

Following command saves result to file:

    ciconia /path/to/file.md > /path/to/file.html

Or using pipe (On Windows in does't work):

    echo "Markdown is **awesome**" | ciconia

### Command Line Options

```
 --gfm                 Activate Gfm extensions
 --compress (-c)       Remove whitespace between HTML tags
 --format (-f)         Output format (html|xhtml) (default: "html")
 --lint (-l)           Syntax check only (lint)
```

### Where is the script?

CLI script will be installed in `vendor/bin/ciconia` by default.
To change the location:

> Yes, there are two ways an alternate vendor binary location can be specified:
>
> 1. Setting the bin-dir configuration setting in composer.json
> 2. Setting the environment variable COMPOSER_BIN_DIR

[http://getcomposer.org/doc/articles/vendor-binaries.md](http://getcomposer.org/doc/articles/vendor-binaries.md)

### Using PHAR version

You can also use [single phar file][phar]

```
ciconia.phar /path/to/file.md
```

If you prefer access this command globally, download [ciconia.phar][phar] and move it into your `PATH`.

```
mv ciconia.phar /usr/local/bin/ciconia
```

Testing
-------

Install or update `dev` dependencies.

```
php composer.phar update --dev
```

and run `phpunit`

## License

The MIT License

## Contributing

Feel free to fork this repository and send pull requests. Take a look at [who has contributed so far][contributors].

## Author

A big thanks to Kazuyuki Hayashi (@kzykhys), who originally created this library.


[milestones]: https://github.com/fluxbb/markdown/issues/milestones
[phar]: https://github.com/fluxbb/markdown/releases/download/v1.0.3/ciconia.phar
[contributors]: https://github.com/fluxbb/markdown/graphs/contributors
[textapi]: https://github.com/kzykhys/Text#api

[ciconia-demo]: http://ciconia.kzykhys.com/
[ciconia-docs]: http://ciconia.kzykhys.com/docs/
[ciconia-syntax]: http://ciconia.kzykhys.com/syntax.html
[ciconia-api]: http://ciconia.kzykhys.com/api/
