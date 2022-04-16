<?php

namespace Bwilliamson\Exporter\Model;

/**
 * Export model
 */
class Export extends \Magento\ImportExport\Model\Export
{
    /**
     * Export data.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        if (isset($this->_data[self::FILTER_ELEMENT_GROUP])) {
            $this->addLogComment(__('Begin export of %1', $this->getEntity()));
            $result = $this->_getEntityAdapter()->setWriter($this->_getWriter())->export();
            if ($this->_getWriter()->getContentType() == 'application/json') {
                $countRows = 1;
            } else {
                $countRows = substr_count($result, "\n");
                if (!$countRows) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('There is no data for the export.'));
                }
            }
            if ($result) {
                $this->addLogComment([__('Exported %1 rows.', $countRows), __('The export is finished.')]);
            }
            return $result;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please provide filter data.'));
        }
    }
}
