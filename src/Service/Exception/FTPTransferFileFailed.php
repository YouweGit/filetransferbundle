<?php


namespace FileTransferBundle\Service\Exception;


class FTPTransferFileFailed extends FTPCommandFailed
{
    public static function createDownloadFileFailed(string $source, string $target, ?string $errorCode): self
    {
        return self::createTransferFileFailed('download', $source, $target, $errorCode);
    }

    public static function createUploadFileFailed(string $source, string $target, ?string $errorCode): self
    {
        return self::createTransferFileFailed('download', $source, $target, $errorCode);
    }

    private static function createTransferFileFailed(
        string $downloadOrUpload,
        string $source,
        string $target,
        ?string $errorCode
    ): self {
        return new self(
            self::createMessage(
                sprintf('"%s" file (source: "%s", target: "%s")', $downloadOrUpload, $source, $target),
                $errorCode
            )
        );
    }
}