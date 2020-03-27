<?php

namespace FileTransferBundle\Service;

use FileTransferBundle\Service\Exception\FTPLoginFailed;

interface FTPServiceBuilderInterface
{
    /**
     *
     *
     * @param string $serverId
     *  The server you want to connect to.
     *
     * @return FTPServiceInterface
     *
     * @throws FTPLoginFailed
     *  If you can not login to the service (e.g. invalid credentials or invalid FTP host address).
     */
    public function login(string $serverId): FTPServiceInterface;
}
