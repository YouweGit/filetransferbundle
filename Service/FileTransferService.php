<?php

namespace FileTransferBundle\Service;

use http\Exception\RuntimeException;
use phpseclib\Net\SFTP;
use Pimcore\Log\ApplicationLogger;

class FileTransferService
{
    const MODE_PUT = 'put';
    const MODE_GET = 'get';

    private $logger;
    private $config;
    private $mode;

    public function __construct(array $parameters, ApplicationLogger $logger)
    {
        $this->config = $parameters;
        $this->logger = $logger;
        $this->logger->setComponent('FileTransfer');

        $this->mode = self::MODE_PUT;
    }

    /**
     * Set the mode where the service should be operating
     *
     * @param string $mode
     */
    public function setMode(string $mode = self::MODE_PUT):void
    {
        $this->mode = $mode;
    }

    /**
     * Returns the mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    public function getRemoteFiles($serverId, $source, $ignore = []): ?array
    {
        if ($this->mode === self::MODE_PUT) {
            return null;
        }

        $selectedFiles = [];

        $sftp = $this->loginInSftp($serverId);
        $files = $sftp->nlist($source);

        if (!$files) {
            $e = "Directory can not be pulled from :" . $source;
            $this->logger->error($e);
        } else {
            foreach ($files as $file) {

                if (in_array($file, $ignore)) {
                    continue;
                }

                $selectedFiles[] = DIRECTORY_SEPARATOR . $file;
            }
        }

        return $selectedFiles;
    }

    public function transferFile($serverid, $sourcefile, $targetfile)
    {
        $sftp = $this->loginInSftp($serverid);

        $sftpFolderName = dirname($targetfile);

        $this->logger->debug("Sftp folder name is $sftpFolderName");

        $this->checkIfDirectoryExists($sftp, $sftpFolderName);

        $this->changeToDirectory($sftp, $sftpFolderName);

        switch ($this->mode) {
            case FileTransferService::MODE_PUT:
                $this->putToServer($sftp, $targetfile, $sourcefile);
                break;
            case FileTransferService::MODE_GET:
                $this->getFromServer($sftp, $targetfile, $sourcefile);
                break;
            default:
                throw new \RuntimeException("There is no mode selected please use -m or --method");
                break;
        }
    }

    /**
     * Login in SFTP and give back the connection
     *
     * @return SFTP
     */
    private function loginInSftp(string $serverid)
    {
        list ($address, $username, $password) = $this->getCredentials($serverid);

        $sftp = new SFTP($address);
        if (!$sftp->login($username, $password)) {
            $e = "Sftp login failed for user " . $username;
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }

        return $sftp;
    }

    /**
     * @return array
     */
    private function getCredentials(string $serverid)
    {
        if (isset($this->config['servers'][$serverid])) {
            $configure = $this->config['servers'][$serverid];

            return [
                $configure['address'],
                $configure['username'],
                $configure['password']
            ];
        } else {
            $e = "Config not found for server id " . $serverid;
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }
    }
    /**
     * Checks if a directory remotely exists
     *
     * @param SFTP $sftp
     * @param string $sftpFolderName
     */
    private function checkIfDirectoryExists(SFTP $sftp, string $sftpFolderName): void
    {
        if (!$sftp->file_exists($sftpFolderName)) {
            if (!$sftp->mkdir($sftpFolderName, 0777)) {
                $e = "Can't create $sftpFolderName directory. " . $sftp->getLastSFTPError();
                $this->logger->error($e);
                throw new \RuntimeException($e);
            }
        } else {
            $this->logger->debug("Directory exists $sftpFolderName");
        }
    }

    /**
     * Change to the correct directory on the server
     *
     * @param SFTP $sftp
     * @param string $targetFile
     * @param string $sourceFile
     */
    private function changeToDirectory(SFTP $sftp, string $sftpFolderName): void
    {
        if (!$sftp->chdir($sftpFolderName)) {
            $e = "Can't chdir to $sftpFolderName directory. " . $sftp->getLastSFTPError();
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }
    }

    /**
     * Upload the file to the server
     *
     * @param SFTP $sftp
     * @param string $targetFile
     * @param string $sourceFile
     */
    private function putToServer(SFTP $sftp, string $targetFile, string $sourceFile): void
    {
        if (!$sftp->put($targetFile, $sourceFile, SFTP::SOURCE_LOCAL_FILE)) {
            $e = "Couldn't send file to sftp. " . $sftp->getLastSFTPError();
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }
    }

    /**
     * Download the file from the remote server
     *
     * @param SFTP $sftp
     * @param string $targetFile
     * @param string $sourceFile
     */
    private function getFromServer(SFTP $sftp, string $targetFile, string $sourceFile): void
    {
        if (!$sftp->get($sourceFile, $targetFile)) {
            $e = "Couldn't get file from sftp. " . $sftp->getLastSFTPError();
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }
    }
}
