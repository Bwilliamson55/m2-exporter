<?php

namespace Bwilliamson\Exporter\Plugin;

use Magento\ImportExport\Controller\Adminhtml\Export\Export;
use Magento\ImportExport\Model\Export as ExportModel;

class BeforeExport
{
    public function beforeExecute(Export $subject, $param)
    {
        $exportFilterKey = ExportModel::FILTER_ELEMENT_GROUP;
        $extraFields = $subject->getRequest()->getPost('myCustomFieldCode');
        if ($subject->getRequest()->getPost($exportFilterKey) && $extraFields) {
            $params = $subject->getRequestParameters();
            //get extra export fields here, and jam them in the filters key
            $subject->getRequest()->setParams($params);
        }
        return $param;
    }
}
