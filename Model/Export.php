<?php

namespace Bwilliamson\Exporter\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\ImportExport\Model\Export\AbstractEntity as ExportEntity;
use Magento\ImportExport\Model\Export\Adapter\Factory;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use Bwilliamson\Exporter\Service\RestService;

/**
 * Custom Export model
 *
 * Will divert custom entities and file formats to our logic
 */
class Export extends \Magento\ImportExport\Model\Export
{
    public const CUSTOM_ENTITY_MAP = [
        'clickup_customer' => 'customer'
    ];
    private RestService $restService;
    private Json $jsonSerializer;

    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param ConfigInterface $exportConfig
     * @param \Magento\ImportExport\Model\Export\Entity\Factory $entityFactory
     * @param Factory $exportAdapterFac
     * @param RestService $restService
     * @param Json $jsonSerializer
     * @param array $data
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        ConfigInterface $exportConfig,
        \Magento\ImportExport\Model\Export\Entity\Factory $entityFactory,
        Factory $exportAdapterFac,
        RestService $restService,
        Json $jsonSerializer,
        array $data = []
    ) {
        parent::__construct(
            $logger,
            $filesystem,
            $exportConfig,
            $entityFactory,
            $exportAdapterFac,
            $data
        );
        $this->restService = $restService;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getEntity(): string
    {
        $entity = parent::getEntity();
        return self::CUSTOM_ENTITY_MAP[$entity] ?? $entity;
    }

    /**
     * @return ExportEntity|AbstractEntity
     * @throws LocalizedException
     */
    protected function _getEntityAdapter()
    {
        //Always set parameters
        $adapter = parent::_getEntityAdapter();
        $adapter->setParameters($this->getData());
        return $adapter;
    }

    /**
     * Export data.
     *
     * @return string
     * @throws LocalizedException
     */
    public function export(): string
    {
        if (isset($this->_data[self::FILTER_ELEMENT_GROUP])) {
            $this->addLogComment(__('Begin export of %1', $this->getEntity()));
//            Examples for other logic
//            $is_json = $this->_getWriter()->getFileExtension() === 'json';
//            $is_rest = substr_count($this->getFileFormat(), 'rest') > 0;

            if (array_key_exists($this->getData('entity'), self::CUSTOM_ENTITY_MAP)) {
                $entities = $this->_exportConfig->getEntities();
                $this->_entityAdapter = $this->_entityFactory->create($entities[$this->getData('entity')]['model']);
            }
            $result = $this->_getEntityAdapter()->setWriter($this->_getWriter())->export();
            $responseBody = '';
            if (is_array($result)) {
                $countRows = 'a json object';
                if ($result['data'] && $result['rest_call']) {
                    $countRows .= "\n Export results:\n";
                    foreach ($result['data'] as $customer) {
                        $result['rest_call']['params']['json'] = $customer;
                        $id = $customer['id'];
                        $response = $this->restService->execute($result['rest_call']);
                        $responseStatus = $response->getStatusCode();
                        $responseBody = $response->getBody();
                        $countRows .= "\n ID: $id ResponseCode: $responseStatus\n";
                    }
                    $countRows .= ' via rest api';
                }
                $data = $this->jsonEncode($result['data']);
                //TODO improve this- stream objects don't encode
                $body = $responseBody ? $this->jsonEncode($responseBody) : '';
                $result = $countRows . "\n" . ($body . "\n" . $data);
            } else {
                $count = substr_count(trim($result), "\n");
                $countRows = $count ? ($count . ' rows') : 0;
            }
            if (!$countRows) {
                throw new LocalizedException(__('There is no data for the export.'));
            }

            if ($result) {
                $this->addLogComment([__('Exported %1', $countRows), __('The export is finished.')]);
            }
            return $result;
        }

        throw new LocalizedException(__('Please provide filter data.'));
    }

    /**
     * @param $object
     * @return bool|string
     */
    public function jsonEncode($object)
    {
        if (is_string($object)) {
            return $object;
        }
        return $this->jsonSerializer->serialize($object);
    }
}
