<?php

namespace FileTransferBundle\Service;

use FileTransferBundle\Service\Exception\FTPLoginFailed;
use FileTransferBundle\Service\Exception\MissingServerConfiguration;
use phpseclib\Net\SFTP;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FTPServiceBuilder implements FTPServiceBuilderInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function login(string $serverId): FTPServiceInterface
    {
        $fileTransferConfig = $this->parameters->get('file_transfer');
        $servers = $fileTransferConfig['servers'];

        if (!isset($servers[$serverId])) {
            throw MissingServerConfiguration::create($serverId);
        }

        $credentials = $servers[$serverId];
        $address = $credentials['address'];
        $username = $credentials['username'];
        $password = $credentials['password'];

        $sftp = new SFTP($address);

        if (!$sftp->login($username, $password)) {
            throw FTPLoginFailed::create($address, $username, $password);
        }

        return new FTPService($sftp);
    }
}
