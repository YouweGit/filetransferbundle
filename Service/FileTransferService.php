<?php

namespace FileTransferBundle\Service;

use phpseclib\Net\SFTP;
use Pimcore\Log\ApplicationLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileTransferService
{
    private $logger;
    private $config;

    public function __construct(ApplicationLogger $logger, ContainerInterface $container)
    {
        $this->config = $container->getParameter('file_transfer_config');
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

        if (!$sftp->file_exists($sftpFolderName)) {
            if (!$sftp->mkdir($sftpFolderName, 0777)) {
                $e = "Can't create $sftpFolderName directory. " . $sftp->getLastSFTPError();
                $this->logger->error($e);
                throw new \RuntimeException($e);
            }
        } else {
            $this->logger->debug("Directory exists $sftpFolderName");
        }

        if (!$sftp->chdir($sftpFolderName)) {
            $e = "Can't chdir to $sftpFolderName directory. " . $sftp->getLastSFTPError();
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }

        if (!$sftp->put($targetfile, $sourcefile, SFTP::SOURCE_LOCAL_FILE)) {
            $e = "Couldn't send file to sftp. " . $sftp->getLastSFTPError();
            $this->logger->error($e);
            throw new \RuntimeException($e);
        }
    }
}
