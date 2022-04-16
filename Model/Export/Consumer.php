<?php
declare(strict_types=1);

namespace Bwilliamson\Exporter\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Api\ExportManagementInterface;
use Magento\ImportExport\Api\Data\ExportInfoInterface;
use Magento\Framework\Notification\NotifierInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Consumer for export message.
 */
class Consumer
{
    private NotifierInterface $notifier;
    private LoggerInterface $logger;
    private ExportManagementInterface $exportManager;
    private Filesystem $filesystem;

    /**
     * Consumer constructor.
     * @param LoggerInterface $logger
     * @param ExportManagementInterface $exportManager
     * @param Filesystem $filesystem
     * @param NotifierInterface $notifier
     */
    public function __construct(
        LoggerInterface $logger,
        ExportManagementInterface $exportManager,
        Filesystem $filesystem,
        NotifierInterface $notifier
    ) {
        $this->logger = $logger;
        $this->exportManager = $exportManager;
        $this->filesystem = $filesystem;
        $this->notifier = $notifier;
    }

    /**
     * Consumer logic.
     *
     * @param ExportInfoInterface $exportInfo
     * @return void
     */
    public function process(ExportInfoInterface $exportInfo)
    {
        $is_json = $exportInfo->getFileFormat() == ('json' || 'rest');
        try {
            $data = $this->exportManager->export($exportInfo);

            //if json- then look deeper and divert to fake filetypes like REST/Graphql
            if ($is_json) {
                $this->logger->critical('JSON export would happen here with this json: ' . $data);
            } else {
                $fileName = $exportInfo->getFileName();
                $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
                $directory->writeFile('export/' . $fileName, $data);
            }

            $this->notifier->addMajor(
                __('Your export file is ready'),
                __('You can pick up your file at export main page')
            );
        } catch (LocalizedException | FileSystemException $exception) {
            $this->notifier->addCritical(
                __('Error during export process occurred'),
                __('Error during export process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while export process. ' . $exception->getMessage());
        }
    }
}
