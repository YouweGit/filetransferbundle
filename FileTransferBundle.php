<?php

namespace FileTransferBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class FileTransferBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/filetransfer/js/pimcore/startup.js'
        ];
    }
}
