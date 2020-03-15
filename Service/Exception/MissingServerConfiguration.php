<?php


namespace FileTransferBundle\Service\Exception;


class MissingServerConfiguration extends \RuntimeException
{
    public static function create(string $serverId): self
    {
        return new self(sprintf('Missing FTP credentials to access server "%s".', $serverId));
    }
}