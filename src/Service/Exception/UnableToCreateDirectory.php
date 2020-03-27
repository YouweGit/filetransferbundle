<?php


namespace FileTransferBundle\Service\Exception;


class UnableToCreateDirectory extends \RuntimeException
{
    public static function createRemoteDirectoryFailed(string $remotePath, string $errorDetail): self
    {
        return self::create('remote', $remotePath, $errorDetail);
    }

    public static function createLocalDirectoryFailed(string $localPath, string $errorDetail): self
    {
        return self::create('local', $localPath, $errorDetail);
    }

    private static function create(string $localOrRemote, string $path, string $errorDetail): self
    {
        return new self(
            sprintf(
                'Can not create "%s" directory "%s" in the local file system. Error detail: "%s".',
                $localOrRemote,
                $path,
                $errorDetail
            )
        );
    }
}