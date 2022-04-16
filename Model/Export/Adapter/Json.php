<?php
namespace Bwilliamson\Exporter\Model\Export\Adapter;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\Framework\Filesystem;


/**
 * Export adapter json.
 */
class Json extends AbstractAdapter
{
    protected string $_delimiter = ',';
    protected string $_enclosure = '"';
    protected Write $_fileHandler;
    private array $_jsonData;
    private JsonSerializer $serializer;

    public function __construct(
        Filesystem $filesystem,
        $destination,
        $destinationDirectoryCode,
        JsonSerializer $serializer,
        array $jsonData = []
    ) {
        $destination = null;
        $destinationDirectoryCode = DirectoryList::VAR_IMPORT_EXPORT;
        parent::__construct($filesystem, $destination, $destinationDirectoryCode);
        $this->serializer = $serializer;
        $this->_jsonData = $jsonData;
    }

    /**
     * Object destructor
     */
    public function __destruct()
    {
        $this->destruct();
    }

    /**
     * Clean cached values
     *
     * @return void
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function destruct()
    {
        if (is_object($this->_fileHandler)) {
            $this->_fileHandler->close();
            $this->resolveDestination();
        }
    }

    /**
     * Remove temporary destination
     *
     * @return void
     * @throws FileSystemException
     * @throws ValidatorException
     */
    private function resolveDestination(): void
    {
        // only temporary file located directly in var folder
        if (strpos($this->_destination, '/') === false) {
            $this->_directoryHandle->delete($this->_destination);
        }
    }

    /**
     * Method called as last step of object instance creation. Can be overridden in child classes.
     *
     * @return $this
     */
    protected function _init(): Json
    {
        $this->_fileHandler = $this->_directoryHandle->openFile($this->_destination, 'w');
        return $this;
    }

    /**
     * MIME-type for 'Content-Type' header.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * Return file extension for downloading.
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'json';
    }

    /**
     * Set column names.
     *
     * @param array $headerColumns
     * @throws Exception
     * @return $this
     */
    public function setHeaderCols(array $headerColumns): Json
    {
        if (null !== $this->_headerCols) {
            throw new LocalizedException(__('The header column names are already set.'));
        }
        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }
            $this->_jsonData['headers'] = $this->_headerCols;
        }
        return $this;
    }

    /**
     * Write row data to source file.
     *
     * @param array $rowData
     * @throws Exception
     * @return $this
     */
    public function writeRow(array $rowData): Json
    {
        if (null === $this->_headerCols) {
            $this->setHeaderCols(array_keys($rowData));
        }
        $this->_jsonData['items'][] =
            array_merge($this->_headerCols, array_intersect_key($rowData, $this->_headerCols));
        return $this;
    }

    public function getContents(): string
    {
        return ($this->serializer->serialize($this->_jsonData));
    }
}
