<?php

namespace Bwilliamson\Exporter\Model\Export;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\CustomerImportExport\Model\Export\Customer;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Custom export for customers
 *
 * Will reduce produced columns on export, and return an array with REST call details as well as data
 */
class ClickupCustomer extends Customer
{
    public const EXPORT_ENTITY_TYPE = 'clickup_customer';
    public const ENTITY_TYPE = 'customer';
    public const URI_ENDPOINT = '/v3/marketing/contacts';
    public const COLUMN_CUSTOMER_ID = 'customer_id';
    public const COLUMNS_EXPORTED = [
        'customer_id',
        'alt_email',
        'alt_phone',
        'confirmation',
        'created_at',
        'created_in',
        'dob',
        'email',
        'emailopt',
        'firstname',
        'gender',
        'group_id',
        'lastname',
        'legacy_id',
        'media_verified',
        'middlename',
        'mobile_phone',
        'prefix',
        'store_id',
        '_store',
        'suffix',
        'updated_at',
        'website_id',
        '_website'
    ];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param CollectionFactory $customerColFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface             $scopeConfig,
        StoreManagerInterface            $storeManager,
        Factory                          $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        TimezoneInterface                $localeDate,
        Config                           $eavConfig,
        CollectionFactory                $customerColFactory,
        array                            $data = ['entity_type_id' => self::ENTITY_TYPE] //Cast our entity type
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $customerColFactory,
            $data
        );
    }

    /**
     * Export given customer data
     *
     * @param \Magento\Customer\Model\Customer $item
     * @return void
     * @throws LocalizedException
     */
    public function exportItem($item): void
    {
        $row = $this->_addAttributeValuesToRow($item);
        $row[self::COLUMN_CUSTOMER_ID] = $item->getEntityId();
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

        $this->getWriter()->writeRow($row);
    }

    /**
     * @throws LocalizedException
     */
    private function getRestConfig(): array
    {
        return [
            'base_uri' => $this->_scopeConfig->getValue(
                'bwilliamson_exporter/clickup_config/base_uri',
                ScopeInterface::SCOPE_WEBSITE
            ),
            'uri_endpoint' => self::URI_ENDPOINT,
            'params' => [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->_scopeConfig->getValue(
                        'bwilliamson_exporter/clickup_config/api_key',
                        ScopeInterface::SCOPE_WEBSITE
                    ),
                    'Content-Type' => $this->getWriter()->getContentType(),
                ],
                'body' => '',
            ],
            'method' => 'put'
        ];
    } //TODO: throw errors when config fields are blank or invalid

    public function _getHeaderColumns(): array
    {
        return self::COLUMNS_EXPORTED;
    }

    public function getEntityTypeCode(): string
    {
        return self::EXPORT_ENTITY_TYPE;
    }

    /**
     * Export process.
     *
     * @throws LocalizedException
     */
    public function export()
    {
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $writer = $this->getWriter();

        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        if ($writer->getFileExtension() !== 'json') {
            return $writer->getContents();
        }

        $customers = $writer->getContents(true);

        $payload = [];
        foreach ($customers['items'] as $customer) {
            $payload[] = array_filter([
                'id' => $customer['customer_id'] ?? '',
                'name' => ($customer['firstname'] ?? '') . " " . ($customer['lastname'] ?? ''),
//TODO
//                'email' => $customer['email'] ?? '',
//                'description' => 'New Task Description',
//                'assignees' => [183],
//                'tags' => ['tag name 1'],
//                'status' => 'Open',
//                'priority' => 3,
//                'due_date' => 1508369194377,
//                'due_date_time' => false,
//                'time_estimate' => 8640000,
//                'start_date' => 1567780450202,
//                'start_date_time' => false,
//                'notify_all' => true,
//                'parent' => null,
//                'links_to' => null,
//                'check_required_custom_fields' => true,
//                'custom_fields' => [
//                    [
//                        'id' => '0a52c486-5f05-403b-b4fd-c512ff05131c',
//                        'value' => 23
//                    ],
//                    [
//                        'id' => '03efda77-c7a0-42d3-8afd-fd546353c2f5',
//                        'value' => 'Text field input'
//                    ]
//                ]
            ]);
        }
        $payload = array_filter($payload);
        $rest_call = $this->getRestConfig();
        return [
            'rest_call' => $rest_call,
            'data' => $payload
        ];
    }
}
