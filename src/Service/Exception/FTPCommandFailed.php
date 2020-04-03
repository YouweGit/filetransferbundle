<?php


namespace FileTransferBundle\Service\Exception;


class FTPCommandFailed extends \Exception
{
    public static function create(string $ftpCommand, ?string $errorCode): self
    {
        return new self(self::createMessage($ftpCommand, $errorCode));
    }

    protected static function createMessage(string $ftpCommand, ?string $errorCode): string
    {
        return sprintf('Failed to execute command "%s". Error code: "%s".', $ftpCommand, $errorCode ?? '');
    }
}