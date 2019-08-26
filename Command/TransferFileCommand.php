<?php

namespace FileTransferBundle\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Console\Dumper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TransferFileCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('transfer:file')
            ->setDescription('Transfer a file')
            ->addArgument(
                'targetserverid',
                InputArgument::REQUIRED,
                'target server identifier')
            ->addArgument(
                'sourcefile',
                InputArgument::REQUIRED,
                'source file')
            ->addArgument(
                'targetfile',
                InputArgument::REQUIRED,
                'target file')
            ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $sourcefile = $input->getArgument('sourcefile');
        $targetfile = $input->getArgument('targetfile');
        $targetserverid = $input->getArgument('targetserverid');

        $service = $this->getContainer()->get('FileTransferBundle\Service\FileTransferService');
//        $config = $this->getContainer()->getParameter('file_transfer_config');

//        var_dump($config);

        $service->transferFile($targetserverid, $sourcefile, $targetfile);

        // retrieve targetserver configuration from config

        // sftp the source file to the target file

    }

}