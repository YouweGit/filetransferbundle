<?php

namespace FileTransferBundle\Command;

use FileTransferBundle\Service\FTPServiceBuilderInterface;
use FileTransferBundle\Service\FTPServiceInterface;
use Pimcore\Console\AbstractCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TransferFileCommand extends AbstractCommand
{
    /**
     * Upload files to FTP server.
     */
    private const OPERATION_UPLOAD = 'put';
    /**
     * Download files from a FTP server.
     */
    private const OPERATION_DOWNLOAD = 'get';


    protected static $defaultName = 'transfer:file';
    /**
     * @var FTPServiceBuilderInterface
     */
    private $ftpServiceBuilder;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(FTPServiceBuilderInterface $ftpServiceBuilder, LoggerInterface $logger)
    {
        parent::__construct();
        $this->ftpServiceBuilder = $ftpServiceBuilder;
        $this->logger = $logger;
    }

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
                "Determine if the service retrieve files or push it to the server. Options: put,get",
                self::OPERATION_UPLOAD
            )
            ->addOption(
                'preservemodifiedtime',
                null,
                InputOption::VALUE_OPTIONAL,
                'If source is a directory, preserve the modified time of its containing files',
                false
            )
            ->addOption(
                'disablesizecheck',
                null,
                InputOption::VALUE_OPTIONAL,
                'Disable file size checking between remote and local',
                false
            )
            ->addOption(
                'removefromsource',
                null,
                InputOption::VALUE_NONE,
                'Remove files from the source server, after they are transferred over'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getArgument('sourcefile');
        $target = $input->getArgument('targetfile');
        $serverId = $input->getArgument('targetserverid');
        $method = $input->getOption('method');
        $preserveModifiedTime = $input->getOption('preservemodifiedtime');
        $disableFileSizeCheck = !($input->getOption('disablesizecheck'));
        $removeFromSource = $input->getOption('removefromsource');

        $ftp = $this->ftpServiceBuilder->login($serverId);

        if ($method === self::OPERATION_DOWNLOAD) {
            $ftp->download($source, $target, $preserveModifiedTime, $disableFileSizeCheck, $removeFromSource);
        } elseif ($method === self::OPERATION_UPLOAD) {
            $ftp->upload($source, $target, $disableFileSizeCheck);
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
     * @param string $serverId
     * @param string $source
     * @param string $target
     */
    private function transferDirectory(
        FTPServiceInterface $ftp,
        string $serverId,
        string $source,
        string $target
    ): void {
        $finder = new Finder();
        $finder->files()->in($source);

        if (!$finder->hasResults()) {
            $this->logger->notice(
                'no files found',
                [
                    'component' => __NAMESPACE__,
                ]
            );

            return;
        }
        foreach ($finder as $file) {
            $destination = $target . $file->getFilename();
            $ftp->transferFile(
                $serverId,
                $file,
                $destination
            );
        }
    }
}
