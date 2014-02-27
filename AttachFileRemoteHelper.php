<?php
namespace Codeception\Module;

/*
 * Based on:
 * http://stackoverflow.com/questions/10559728/uploading-files-remotely-on-selenium-webdriver-using-php/10560052#10560052
 *
 * @author Nils Langner <nils.langner@thewebhatesme.com>
 *
 */


/**
 * This class is only used to get access to the findField() method from the webDriver class. If that 
 * method was public a much cleaner inplementation was possible. But it works, so I guss it's a good place
 * to start.
 */
class AttachFileWebDriver extends WebDriver
{
    public $myWebDriver;

    public function __construct($webdriver=null)
    {
        $this->myWebDriver = $webdriver;
    }

    public function fillField($field, $value)
    {
        $el = $this->myWebDriver->findField($field);
        $el->sendKeys($value);
    }
}

class AttachFileRemoteHelper extends \Codeception\Module
{
    /**
     * This function zips the file that has to be transfered
     * and returns the base64 encoded content.
     *
     * @param filename $value
     * @param the file extension $file_extension
     * @throws \Exception
     * @return string the remote file name
     */
    private function getZippedFile ($value, $file_extension = "")
    {
        $zip = new \ZipArchive();

        $filename_hash = sha1(time() . $value);

        $zip_filename = "{$filename_hash}_zip.zip";
        if ($zip->open($zip_filename, \ZipArchive::CREATE) === false) {
            throw new \Exception('file_get_contents failed');
        }

        $file_data = @file_get_contents($value);
        if ($file_data === false) {
            throw new \Exception('Can\'t open file '.$value);
        }

        $filename = "{$filename_hash}.{$file_extension}";
        if (@file_put_contents($filename, $file_data) === false) {
            throw new \Exception('Unable to store temporary file.');
        }

        $zip->addFile($filename, "{$filename_hash}.{$file_extension}");
        $zip->close();

        $zip_file = @file_get_contents($zip_filename);
        if ($zip_file === false) {
            throw new \Exception('Unable to open created zip file');
        }

        $zippedFile = base64_encode($zip_file);

        return $zippedFile;
    }

    /**
     * This function uploads a file to the remote server and
     * returns the remote filename. This filename can be used when
     * attaching a file on a website.
     *
     * @param string $filename the name of the file that will
     *                         bei uploaded
     * @return string the remote file name
     */
    private function uploadRemoteFile ($filename)
    {
        $codeCWebdriver = $this->getModule("WebDriver");
        // @var $codeCWebdriver RemoteWebDriver

        $executor = $codeCWebdriver->webDriver->getCommandExecutor();
        /* @var \WebDriverCommandExecutor $executor */

        $remoteFileName = $executor->execute("sendFile", array(
                "file" => $this->getZippedFile(realpath(\Codeception\Configuration::dataDir() . $filename))
        ));

        return (string)$remoteFileName;
    }

    /**
     * This function attaches a file even if using a remote connection
     * via WebDriver / Selenium Server
     * 
     * @param string $field the field locator
     * @param string $filename the filename in the data directory
     */
    public function attachFileRemote($field, $filename)
    {
        $remoteFileName = $this->uploadRemoteFile($filename);

        $webDriver = new AttachFileWebDriver($this->getModule("WebDriver"));
        $webDriver->fillField((string)$field, $remoteFileName);
    }
}
