<?php

namespace FileTransferBundle\Service;

use phpseclib\Net\SFTP;
use Pimcore\Log\ApplicationLogger;

class FileTransferService
{
    private $logger;
    private $config;

    public function __construct(array $parameters, ApplicationLogger $logger)
    {
        $this->config = $parameters;
        $this->logger = $logger;
        $this->logger->setComponent('FileTransfer');

    }

    public function transferFile($serverid, $sourcefile, $targetfile)
    {
        if (isset($this->config['servers'][$serverid])) {
            $c = $this->config['servers'][$serverid];
            $address = $c['address'];
            $username = $c['username'];
            $password = $c['password'];
        } else {
            $e = "Config not found for server id " . $serverid;
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }

        $sftp = new SFTP($address);
        if (!$sftp->login($username, $password)) {
            $e = "Sftp login failed for user " . $username;
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }

        $sftpFolderName = dirname($targetfile);

        $this->logger->debug("Sftp folder name is $sftpFolderName");

        $this->checkIfDirectoryExists($sftp, $sftpFolderName);

        $this->changeToDirectory($sftpFolderName);

        $this->putToServer($sftp, $targetfile, $sourcefile);
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
}
