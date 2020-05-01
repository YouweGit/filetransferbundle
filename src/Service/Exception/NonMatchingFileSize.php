<?php


namespace FileTransferBundle\Service\Exception;


class NonMatchingFileSize extends \Exception
{
    public static function create(int $remoteFileSize, int $localFileSize): self
    {
        return new self(
            sprintf(
                'The download file size is different from the remote file size. Remote file has "%d" bytes, and the local file has "%d" bytes.',
                $remoteFileSize,
                $localFileSize
            )
        );
    }
}
