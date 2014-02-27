# Codeception Remote File Attachment Helper

This module helps to upload files when using webdriver via remote connection when using [codeception](http://www.codeception.com/ "Codeception"). 

## Getting started

### Bootstrap
Just copy the file to your project and add it to your bootstrap file:

<code>include_once "/path/to/module/AttachFileRemoteHelper.php";</code>

### Configuration
After editing your bootstrap file you have to upade your test suite configuration

```
modules:
    enabled: [WebDriver, AttachFileRemoteHelper]
```

No additional configuration has to be made. Just add the AttachFileRemoteHelper.

### Building the WebGuy

After changing your configuration you have to re-build the web guy. 

``` 
php codecept.phar build
```

## Usage

Once the module is actived you are able to use the new method <code>attachFileRemote</code> the same way you are using the native codeption/webdriver method arrachFile.

```php
<?php

class CreateCommentCest
{
    public function testRemoteFileUpload (WebGuy $I, $scenario)
    {
        $I->amOnPage("/html/formulare/anzeige/input_file.htm");
        $I->attachFileRemote("Datei", "image.png");
    }
}
```
