<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Index
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Index\Block\Adminhtml\Process;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'process_id';
        $this->_controller = 'adminhtml_process';
        $this->_blockGroup = 'Magento_Index';

        parent::_construct();

        $this->_updateButton('save', 'label', __('Save Process'));
        if ($this->_coreRegistry->registry('current_index_process')) {
            $this->_addButton(
                'reindex',
                array('label' => __('Reindex Data'), 'onclick' => "setLocation('{$this->getRunUrl()}')")
            );
        }
        $this->_removeButton('reset');
        $this->_removeButton('delete');
    }

    /**
     * Get back button url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/process/list');
    }

    /**
     * Get process reindex action url
     *
     * @return string
     */
    public function getRunUrl()
    {
        return $this->getUrl(
            'adminhtml/process/reindexProcess',
            array('process' => $this->_coreRegistry->registry('current_index_process')->getId())
        );
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        $process = $this->_coreRegistry->registry('current_index_process');
        if ($process && $process->getId()) {
            return __("'%1' Index Process Information", $process->getIndexer()->getName());
        }
    }
}
