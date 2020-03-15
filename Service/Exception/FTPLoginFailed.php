<?php


namespace FileTransferBundle\Service\Exception;


class FTPLoginFailed extends \Exception
{
    public static function create(string $serverAddress, string $username, string $password): self
    {
        return new self(
            sprintf(
                'Failed to login on FTP server "%s" with username "%s" (Using password: "%s").',
                $serverAddress,
                $username,
                $password ? 'YES' : 'NO'
            )
        );
    }
}