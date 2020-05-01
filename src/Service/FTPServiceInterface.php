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
     * @param bool $disableFileSizeCheck
     * @throws FTPCommandFailed
     * @throws FTPTransferFileFailed
     */
    public function download(
        string $remotePath,
        string $localPath,
        bool $preserveModifiedTime,
        bool $disableFileSizeCheck
    ): void;

    /**
     * @param string $localPath
     * @param string $remotePath
     * @param bool $disableFileSizeCheck
     * @throws FTPCommandFailed
     * @throws FTPTransferFileFailed
     */
    public function upload(string $localPath, string $remotePath, bool $disableFileSizeCheck): void;
}
