<?php


namespace FileTransferBundle\Service\Exception;


class DirectoryIsNotWritable extends \RuntimeException
{
    public static function createRemoteDirectory(string $remotePath): self
    {
        return self::create('remote', $remotePath);
    }

    public static function createLocalDirectory(string $localPath): self
    {
        return self::create('local', $localPath);
    }

    private static function create(string $localOrRemote, string $path): self
    {
        return new self(
            sprintf(
                'The "%s" directory is not writable: "%s".',
                $localOrRemote,
                $path
            )
        );
    }
}