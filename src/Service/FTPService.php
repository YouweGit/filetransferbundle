<?php

namespace FileTransferBundle\Service;

use FileTransferBundle\Service\Exception\DirectoryIsNotWritable;
use FileTransferBundle\Service\Exception\FTPCommandFailed;
use FileTransferBundle\Service\Exception\FTPTransferFileFailed;
use FileTransferBundle\Service\Exception\NonMatchingFileSize;
use FileTransferBundle\Service\Exception\RemoveFileFailed;
use FileTransferBundle\Service\Exception\UnableToCreateDirectory;
use phpseclib\Net\SFTP;
use Zend\Stdlib\ErrorHandler;

class FTPService implements FTPServiceInterface
{
    /**
     * @var SFTP
     */
    private $ftp;

    public function __construct(SFTP $ftp)
    {
        $this->ftp = $ftp;
    }

    public function download(
        string $remotePath,
        string $localPath,
        bool $preserveModifiedTime,
        bool $checkFileSize,
        bool $removeFromSource
    ): void {
        $remoteFiles = (array)$remotePath;
        if ($this->ftp->is_dir($remotePath)) {
            $remoteFiles = $this->ls($remotePath, true);
        }

        foreach ($remoteFiles as $remoteFile) {
            if ($this->ftp->is_dir($remoteFile)) {
                $this->download(
                    $remoteFile,
                    rtrim($localPath, DIRECTORY_SEPARATOR) . $remoteFile,
                    $preserveModifiedTime,
                    $checkFileSize,
                    $removeFromSource
                );
            } else {
                $realLocalPath = str_replace(
                    rtrim($remotePath, DIRECTORY_SEPARATOR),
                    rtrim($localPath, DIRECTORY_SEPARATOR),
                    $remoteFile
                );
                $localDirectory = dirname($realLocalPath);

                if (!file_exists($localDirectory)) {
                    try {
                        ErrorHandler::start();
                        mkdir($localDirectory, 0777, true);
                        ErrorHandler::stop(true);
                    } catch (\Throwable $e) {
                        throw UnableToCreateDirectory::createLocalDirectoryFailed($localDirectory, $e->getMessage());
                    }
                }
                if (!is_writable($localDirectory)) {
                    throw DirectoryIsNotWritable::createLocalDirectory($localDirectory);
                }
                if (!$this->ftp->get($remoteFile, $realLocalPath)) {
                    throw FTPTransferFileFailed::createDownloadFileFailed(
                        $remoteFile,
                        $realLocalPath,
                        $this->ftp->getLastError()
                    );
                }

                $this->checkSize($checkFileSize, $remoteFile, $realLocalPath);

                if ($preserveModifiedTime) {
                    $originalFileModifiedTime = $this->ftp->filemtime($remoteFile);
                    touch($realLocalPath, $originalFileModifiedTime);
                }

                if ($removeFromSource) {
                    if (!$this->ftp->delete($remoteFile)) {
                        throw RemoveFileFailed::createRemoveFileFromRemoteFailed(
                            $remoteFile,
                            $this->ftp->getLastError()
                        );
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function upload(string $localPath, string $remotePath, bool $checkFileSize): void
    {
        $localFiles = (array)$localPath;
        if (is_dir($localPath)) {
            $localFiles = array_map(
                function (string $item) use ($localPath) {
                    return rtrim($localPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item;
                },
                array_filter(
                    scandir($localPath, SCANDIR_SORT_ASCENDING),
                    function (string $item) {
                        return $item != '.' && $item != '..';
                    }
                )
            );
        }

        foreach ($localFiles as $localFile) {
            if (is_dir($localFile)) {
                $this->upload($localFile, rtrim($remotePath, DIRECTORY_SEPARATOR) . $localFile, $checkFileSize);
            } else {
                $realRemotePath = str_replace(
                    rtrim($localPath, DIRECTORY_SEPARATOR),
                    rtrim($remotePath, DIRECTORY_SEPARATOR),
                    $localFile
                );
                $remoteDirectory = dirname($realRemotePath);
                if (!$this->ftp->file_exists($remoteDirectory)) {
                    try {
                        ErrorHandler::start();
                        $this->ftp->mkdir($remoteDirectory, 0777, true);
                        ErrorHandler::stop(true);
                    } catch (\Throwable $e) {
                        throw UnableToCreateDirectory::createRemoteDirectoryFailed($remoteDirectory, $e->getMessage());
                    }
                }
                if (!$this->ftp->is_writable($remoteDirectory)) {
                    throw DirectoryIsNotWritable::createRemoteDirectory($remoteDirectory);
                }
                if (!$this->ftp->put($realRemotePath, $localPath, SFTP::SOURCE_LOCAL_FILE)) {
                    throw FTPTransferFileFailed::createDownloadFileFailed(
                        $localFile,
                        $realRemotePath,
                        $this->ftp->getLastError()
                    );
                }

                $this->checkSize($checkFileSize, $realRemotePath, $localPath);
            }
        }
    }

    private function ls(string $source, bool $recursive): array
    {
        $files = $this->ftp->nlist($source, $recursive);

        if (!$files) {
            throw FTPCommandFailed::create('nlist', sprintf('"%s"', implode('", "', $this->ftp->getErrors())));
        }

        $files = array_map(
            function (string $file) use ($source) {
                return rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
            },
            array_filter(
                $files,
                function (string $file) {
                    return $file != '.' && $file != '..';
                }
            )
        );

        sort($files);

        return $files;
    }

    /**
     * @param bool $checkFileSize
     * @param $remoteFile
     * @param $realLocalPath
     * @throws NonMatchingFileSize
     */
    private function checkSize(bool $checkFileSize, string $remoteFile, string $realLocalPath): void
    {
        $sizeRemote = $this->ftp->size($remoteFile);
        $sizeLocal = filesize($realLocalPath);

        if ($checkFileSize && $sizeRemote !== $sizeLocal) {
            throw NonMatchingFileSize::create($sizeRemote, $sizeLocal);
        }
    }
}
