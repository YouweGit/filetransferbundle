<?php

namespace FileTransferBundle\Service;

use FileTransferBundle\Service\Exception\FTPCommandFailed;
use FileTransferBundle\Service\Exception\FTPTransferFileFailed;

interface FTPServiceInterface
{
    /**
     * @param string $remotePath
     * @param string $localPath
     * @throws FTPCommandFailed
     * @throws FTPTransferFileFailed
     */
    public function download(string $remotePath, string $localPath): void;

    /**
     * @param string $localPath
     * @param string $remotePath
     * @throws FTPCommandFailed
     * @throws FTPTransferFileFailed
     */
    public function upload(string $localPath, string $remotePath): void;
}