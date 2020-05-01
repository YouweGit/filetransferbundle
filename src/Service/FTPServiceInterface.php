<?php

namespace FileTransferBundle\Service;

use FileTransferBundle\Service\Exception\FTPCommandFailed;
use FileTransferBundle\Service\Exception\FTPTransferFileFailed;

interface FTPServiceInterface
{
    /**
     * @param string $remotePath
     * @param string $localPath
     * @param bool $preserveModifiedTime
     * @param bool $checkFileSize
     * @throws FTPCommandFailed
     * @throws FTPTransferFileFailed
     */
    public function download(
        string $remotePath,
        string $localPath,
        bool $preserveModifiedTime,
        bool $checkFileSize
    ): void;

    /**
     * @param string $localPath
     * @param string $remotePath
     * @param bool $checkFileSize
     * @throws FTPCommandFailed
     * @throws FTPTransferFileFailed
     */
    public function upload(string $localPath, string $remotePath, bool $checkFileSize): void;
}
