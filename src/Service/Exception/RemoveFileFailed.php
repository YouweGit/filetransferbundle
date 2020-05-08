<?php


namespace FileTransferBundle\Service\Exception;


class RemoveFileFailed extends \RuntimeException
{
    public static function createRemoveFileFromRemoteFailed(string $remotePath, ?string $errorCode): self
    {
        return self::create('remote', $remotePath, $errorCode);
    }

    private static function create(string $localOrRemote, string $path, ?string $errorCode): self
    {
        return new self(
            sprintf(
                'The "%s" path could not be deleted: "%s" error "%s".',
                $localOrRemote,
                $path,
                $errorCode ?? ''
            )
        );
    }
}