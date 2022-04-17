<?php

namespace Bwilliamson\Exporter\Block\Adminhtml\Export\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\ImportExport\Model\Source\Export\EntityFactory;
use Magento\ImportExport\Model\Source\Export\FormatFactory;

class Form extends Generic
{
    protected EntityFactory $_entityFactory;
    protected FormatFactory $_formatFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EntityFactory $entityFactory
     * @param FormatFactory $formatFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EntityFactory $entityFactory,
        FormatFactory $formatFactory,
        array $data = []
    ) {
        $this->_entityFactory = $entityFactory;
        $this->_formatFactory = $formatFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML.
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm(): Form
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('adminhtml/*/getFilter'),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Export Settings')]);
        $fieldset->addField(
            'entity',
            'select',
            [
                'name' => 'entity',
                'title' => __('Entity Type'),
                'label' => __('Entity Type'),
                'required' => false,
                'onchange' => 'varienExport.getFilter();',
                'values' => $this->_entityFactory->create()->toOptionArray()
            ]
        );
        $fieldset->addField(
            'file_format',
            'select',
            [
                'name' => 'file_format',
                'title' => __('Export File Format'),
                'label' => __('Export File Format'),
                'required' => false,
                'values' => $this->_formatFactory->create()->toOptionArray()
            ]
        );
        $fieldset->addField(
            \Magento\ImportExport\Model\Export::FIELDS_ENCLOSURE,
            'checkbox',
            [
                'name' => \Magento\ImportExport\Model\Export::FIELDS_ENCLOSURE,
                'label' => __('Fields Enclosure'),
                'title' => __('Fields Enclosure'),
                'value' => 1,
            ]
        );

        //Add custom export fields here for temporary export data

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
