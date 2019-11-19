<?php

namespace FileTransferBundle\Command;

use FileTransferBundle\Service\FileTransferService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Console\Dumper;
use Pimcore\Log\ApplicationLogger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TransferFileCommand extends AbstractCommand
{
    protected static $defaultName = 'transfer:file';
    /**
     * @var FileTransferService
     */
    private $fileTransferService;

    protected function configure()
    {
        $this
            ->setDescription('Transfer a file')
            ->addArgument(
                'targetserverid',
                InputArgument::REQUIRED,
                'target server identifier'
            )
            ->addArgument(
                'sourcefile',
                InputArgument::REQUIRED,
                'source file'
            )
            ->addArgument(
                'targetfile',
                InputArgument::REQUIRED,
                'target file'
            )
            ->addOption(
                'method',
                'm',
                InputOption::VALUE_OPTIONAL,
                "Determen if the service retreive files or push it to the server. Options: put,get",
                FileTransferService::MODE_PUT
            )
            ->addOption(
                'ignore',
                'i',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                '',
                []
            );
    }

    /**
     * TransferFileCommand constructor.
     * @param FileTransferService $fileTransferService
     */
    public function __construct(FileTransferService $fileTransferService)
    {
        $this->fileTransferService = $fileTransferService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourcefile = $input->getArgument('sourcefile');
        $targetfile = $input->getArgument('targetfile');
        $targetserverid = $input->getArgument('targetserverid');


        /** @var FileTransferService $service */
        $service = $this->fileTransferService;
        $service->setMode($input->getOption('method'));

        if ($service->getMode() === FileTransferService::MODE_GET) {
            $files = $service->getRemoteFiles($targetserverid, $sourcefile, $this->input->getOption('ignore', []));
            if (is_array($files)) {
                foreach ($files as $file) {
                    $service->transferFile(
                        $targetserverid,
                        $sourcefile.$file,
                        $targetfile.$file
                    );
                }
            }
        } else {
            if ($this->useDirectoryMode($sourcefile)) {
                $this->transferDirectory($service, $targetserverid, $sourcefile, $targetfile);
            } else {
                $service->transferFile($targetserverid, $sourcefile, $targetfile);
            }
        }
    }

    /**
     * Checks if the transfer is in directory mode
     * @param string $source
     * @param string $target
     * @return bool
     */
    private function useDirectoryMode(string $source): bool
    {
        if (is_dir($source)) {
            return true;
        }
        return false;
    }

    /**
     * Transfers the a complete directory to a remote server
     * @param FileTransferService $service
     * @param string              $serverId
     * @param string              $source
     * @param string              $target
     */
    private function transferDirectory(
        FileTransferService $service,
        string $serverId,
        string $source,
        string $target
    ): void {
        $finder = new Finder();
        $finder->files()->in($source);

        /** @var ApplicationLogger $logger */
        $logger = $this->getContainer()->get('monolog.logger.admin');
        if (!$finder->hasResults()) {
            $logger->notice(
                'no files found',
                [
                    'component' => 'FileTransfer',
                ]
            );
            return;
        }
        foreach ($finder as $file) {
            $destination = $target.$file->getFilename();
            $service->transferFile(
                $serverId,
                $file,
                $destination
            );
        }
    }
}
