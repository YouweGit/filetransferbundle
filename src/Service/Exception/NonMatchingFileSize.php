<?php


namespace FileTransferBundle\Service\Exception;


class NonMatchingFileSize extends \Exception
{
    public static function create(int $remoteFileSize, int $localFileSize)
    {
        return new self(
            sprintf("Remote file size and local file size don't match (%d) from remote and (%d) from local", $remoteFileSize, $localFileSize)
        );
    }
}
