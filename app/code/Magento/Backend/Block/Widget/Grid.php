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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Widget;

/**
 * Backend grid widget block
 *
 * @method string getRowClickCallback() getRowClickCallback()
 * @method \Magento\Backend\Block\Widget\Grid setRowClickCallback() setRowClickCallback(string $value)
 */
class Grid extends \Magento\Backend\Block\Widget
{
    /**
     * Page and sorting var names
     *
     * @var string
     */
    protected $_varNameLimit = 'limit';

    /**
     * @var string
     */
    protected $_varNamePage = 'page';

    /**
     * @var string
     */
    protected $_varNameSort = 'sort';

    /**
     * @var string
     */
    protected $_varNameDir = 'dir';

    /**
     * @var string
     */
    protected $_varNameFilter = 'filter';

    /**
     * @var int
     */
    protected $_defaultLimit = 20;

    /**
     * @var int
     */
    protected $_defaultPage = 1;

    /**
     * @var bool|string
     */
    protected $_defaultSort = false;

    /**
     * @var string
     */
    protected $_defaultDir = 'desc';

    /**
     * @var array
     */
    protected $_defaultFilter = array();

    /**
     * Empty grid text
     *
     * @var string|null
     */
    protected $_emptyText;

    /**
     * Empty grid text CSS class
     *
     * @var string|null
     */
    protected $_emptyTextCss = 'empty-text';

    /**
     * Pager visibility
     *
     * @var boolean
     */
    protected $_pagerVisibility = true;

    /**
     * Massage block visibility
     *
     * @var boolean
     */
    protected $_messageBlockVisibility = false;

    /**
     * Should parameters be saved in session
     *
     * @var bool
     */
    protected $_saveParametersInSession = false;

    /**
     * Count totals
     *
     * @var boolean
     */
    protected $_countTotals = false;

    /**
     * Totals
     *
     * @var \Magento\Framework\Object
     */
    protected $_varTotals;

    /**
     * RSS list
     *
     * @var \Magento\Framework\Object[]
     */
    protected $_rssLists = array();

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid.phtml';

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = array()
    ) {
        $this->_backendHelper = $backendHelper;
        $this->_backendSession = $context->getBackendSession();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->getRowClickCallback()) {
            $this->setRowClickCallback('openGridRow');
        }

        if ($this->hasData('id')) {
            $this->setId($this->getData('id'));
        }

        if ($this->hasData('default_sort')) {
            $this->setDefaultSort($this->getData('default_sort'));
        }

        if ($this->hasData('default_dir')) {
            $this->setDefaultDir($this->getData('default_dir'));
        }

        if ($this->hasData('save_parameters_in_session')) {
            $this->setSaveParametersInSession($this->getData('save_parameters_in_session'));
        }

        $this->setPagerVisibility(
            $this->hasData('pager_visibility') ? (bool)$this->getData('pager_visibility') : true
        );

        $this->setData('use_ajax', $this->hasData('use_ajax') ? (bool)$this->getData('use_ajax') : false);

        if ($this->hasData('rssList') && is_array($this->getData('rssList'))) {
            foreach ($this->getData('rssList') as $item) {
                $this->addRssList($item['url'], $item['label']);
            }
        }
    }

    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     */
    public function setCollection($collection)
    {
        $this->setData('dataSource', $collection);
    }

    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->getData('dataSource');
    }

    /**
     * Retrieve column set block
     *
     * @return \Magento\Backend\Block\Widget\Grid\ColumnSet
     */
    public function getColumnSet()
    {
        return $this->getChildBlock('grid.columnSet');
    }

    /**
     * Retrieve export block
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getExportBlock()
    {
        if (!$this->getChildBlock('grid.export')) {
            throw new \Magento\Framework\Model\Exception('Export block for grid ' . $this->getNameInLayout() . ' is not defined');
        }
        return $this->getChildBlock('grid.export');
    }

    /**
     * Retrieve list of grid columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->getColumnSet()->getColumns();
    }

    /**
     * Count grid columns
     *
     * @return int
     */
    public function getColumnCount()
    {
        return count($this->getColumns());
    }

    /**
     * Retrieve column by id
     *
     * @param string $columnId
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getColumn($columnId)
    {
        return $this->getColumnSet()->getChildBlock($columnId);
    }

    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset(
                $data[$columnId]
            ) && (is_array(
                $data[$columnId]
            ) && !empty($data[$columnId]) || strlen(
                $data[$columnId]
            ) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    /**
     * Add column filtering conditions to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $condition = $column->getFilter()->getCondition();
                if ($field && isset($condition)) {
                    $this->getCollection()->addFieldToFilter($field, $condition);
                }
            }
        }
        return $this;
    }

    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }

    /**
     * Get prepared collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getPreparedCollection()
    {
        $this->_prepareCollection();
        return $this->getCollection();
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->_backendHelper->prepareFilterString($filter);
                $data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
                $this->_setFilterValues($data);
            } else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } else if (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if ($this->getColumn($columnId) && $this->getColumn($columnId)->getIndex()) {
                $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
                $this->getColumn($columnId)->setDir($dir);
                $this->_setCollectionOrder($this->getColumn($columnId));
            }
        }

        return $this;
    }

    /**
     * Decode URL encoded filter value recursive callback method
     *
     * @param string &$value
     * @return void
     */
    protected function _decodeFilter(&$value)
    {
        $value = $this->_backendHelper->decodeFilter($value);
    }

    /**
     * Apply pagination to collection
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize((int)$this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        $this->getCollection()->setCurPage((int)$this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _prepareGrid()
    {
        if ($this->getChildBlock('grid.massaction') && $this->getChildBlock('grid.massaction')->isAvailable()) {
            $this->getChildBlock('grid.massaction')->prepareMassactionColumn();
        }

        $this->_prepareCollection();
        if ($this->hasColumnRenderers()) {
            foreach ($this->getColumnRenderers() as $renderer => $rendererClass) {
                $this->getColumnSet()->setRendererType($renderer, $rendererClass);
            }
        }
        if ($this->hasColumnFilters()) {
            foreach ($this->getColumnFilters() as $filter => $filterClass) {
                $this->getColumnSet()->setFilterType($filter, $filterClass);
            }
        }
        $this->getColumnSet()->setSortable($this->getSortable());
        $this->_prepareFilterButtons();
    }

    /**
     * Get massaction block
     *
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function getMassactionBlock()
    {
        return $this->getChildBlock('grid.massaction');
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                array('label' => __('Reset Filter'), 'onclick' => $this->getJsObjectName() . '.resetFilter()', 'class' => 'action-reset')
            )
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                array(
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'task'
                )
            )
        );
    }

    /**
     * Initialize grid before rendering
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareGrid();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve limit request key
     *
     * @return string
     */
    public function getVarNameLimit()
    {
        return $this->_varNameLimit;
    }

    /**
     * Retrieve page request key
     *
     * @return string
     */
    public function getVarNamePage()
    {
        return $this->_varNamePage;
    }

    /**
     * Retrieve sort request key
     *
     * @return string
     */
    public function getVarNameSort()
    {
        return $this->_varNameSort;
    }

    /**
     * Retrieve sort direction request key
     *
     * @return string
     */
    public function getVarNameDir()
    {
        return $this->_varNameDir;
    }

    /**
     * Retrieve filter request key
     *
     * @return string
     */
    public function getVarNameFilter()
    {
        return $this->_varNameFilter;
    }

    /**
     * Set Limit request key
     *
     * @param string $name
     * @return $this
     */
    public function setVarNameLimit($name)
    {
        $this->_varNameLimit = $name;
        return $this;
    }

    /**
     * Set Page request key
     *
     * @param string $name
     * @return $this
     */
    public function setVarNamePage($name)
    {
        $this->_varNamePage = $name;
        return $this;
    }

    /**
     * Set Sort request key
     *
     * @param string $name
     * @return $this
     */
    public function setVarNameSort($name)
    {
        $this->_varNameSort = $name;
        return $this;
    }

    /**
     * Set Sort Direction request key
     *
     * @param string $name
     * @return $this
     */
    public function setVarNameDir($name)
    {
        $this->_varNameDir = $name;
        return $this;
    }

    /**
     * Set Filter request key
     *
     * @param string $name
     * @return $this
     */
    public function setVarNameFilter($name)
    {
        $this->_varNameFilter = $name;
        return $this;
    }

    /**
     * Set visibility of pager
     *
     * @param bool $visible
     * @return $this
     */
    public function setPagerVisibility($visible = true)
    {
        $this->_pagerVisibility = $visible;
        return $this;
    }

    /**
     * Return visibility of pager
     *
     * @return bool
     */
    public function getPagerVisibility()
    {
        return $this->_pagerVisibility;
    }

    /**
     * Set visibility of message blocks
     *
     * @param bool $visible
     * @return void
     */
    public function setMessageBlockVisibility($visible = true)
    {
        $this->_messageBlockVisibility = $visible;
    }

    /**
     * Return visibility of message blocks
     *
     * @return bool
     */
    public function getMessageBlockVisibility()
    {
        return $this->_messageBlockVisibility;
    }

    /**
     * Set default limit
     *
     * @param int $limit
     * @return $this
     */
    public function setDefaultLimit($limit)
    {
        $this->_defaultLimit = $limit;
        return $this;
    }

    /**
     * Set default page
     *
     * @param int $page
     * @return $this
     */
    public function setDefaultPage($page)
    {
        $this->_defaultPage = $page;
        return $this;
    }

    /**
     * Set default sort
     *
     * @param string $sort
     * @return $this
     */
    public function setDefaultSort($sort)
    {
        $this->_defaultSort = $sort;
        return $this;
    }

    /**
     * Set default direction
     *
     * @param string $dir
     * @return $this
     */
    public function setDefaultDir($dir)
    {
        $this->_defaultDir = $dir;
        return $this;
    }

    /**
     * Set default filter
     *
     * @param string $filter
     * @return $this
     */
    public function setDefaultFilter($filter)
    {
        $this->_defaultFilter = $filter;
        return $this;
    }

    /**
     * Retrieve rss lists types
     *
     * @return \Magento\Framework\Object[]|false
     */
    public function getRssLists()
    {
        return empty($this->_rssLists) ? false : $this->_rssLists;
    }

    /**
     * Add new rss list to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  $this
     */
    public function addRssList($url, $label)
    {
        $this->_rssLists[] = new \Magento\Framework\Object(
            array('url' => $this->getUrl($url, array('_nosecret' => true)), 'label' => $label)
        );
        return $this;
    }

    /**
     * Clear rss list in grid
     *
     * @return  $this
     */
    public function clearRss()
    {
        $this->_rssLists = array();
        return $this;
    }

    /**
     * Check whether grid container should be displayed
     *
     * @return bool
     */
    public function canDisplayContainer()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve grid reload url
     *
     * @return string;
     */
    public function getGridUrl()
    {
        return $this->hasData('grid_url') ? $this->getData('grid_url') : $this->getAbsoluteGridUrl();
    }

    /**
     * Grid url getter
     * Version of getGridUrl() but with parameters
     *
     * @param array $params url parameters
     * @return string current grid url
     */
    public function getAbsoluteGridUrl($params = array())
    {
        return $this->getCurrentUrl($params);
    }

    /**
     * Retrieve grid
     *
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    public function getParam($paramName, $default = null)
    {
        $sessionParamName = $this->getId() . $paramName;
        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            if ($this->_saveParametersInSession) {
                $this->_backendSession->setData($sessionParamName, $param);
            }
            return $param;
        } elseif ($this->_saveParametersInSession && ($param = $this->_backendSession->getData($sessionParamName))) {
            return $param;
        }

        return $default;
    }

    /**
     * Set whether grid parameters should be saved in session
     *
     * @param bool $flag
     * @return $this
     */
    public function setSaveParametersInSession($flag)
    {
        $this->_saveParametersInSession = $flag;
        return $this;
    }

    /**
     * Retrieve grid javascript object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId() . 'JsObject';
    }

    /**
     * Set count totals
     *
     * @param bool $count
     * @return $this
     */
    public function setCountTotals($count = true)
    {
        $this->_countTotals = $count;
        return $this;
    }

    /**
     * Return count totals
     *
     * @return bool
     */
    public function getCountTotals()
    {
        return $this->_countTotals;
    }

    /**
     * Set totals
     *
     * @param \Magento\Framework\Object $totals
     * @return void
     */
    public function setTotals(\Magento\Framework\Object $totals)
    {
        $this->_varTotals = $totals;
    }

    /**
     * Retrieve totals
     *
     * @return \Magento\Framework\Object
     */
    public function getTotals()
    {
        return $this->_varTotals;
    }

    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getColumnSet()->isFilterVisible()) {
            $html .= $this->getResetFilterButtonHtml();
            $html .= $this->getSearchButtonHtml();
        }
        return $html;
    }

    /**
     * Generate reset button
     *
     * @return string
     */
    public function getResetFilterButtonHtml()
    {
        return $this->getChildHtml('reset_filter_button');
    }

    /**
     * Generate search button
     *
     * @return string
     */
    public function getSearchButtonHtml()
    {
        return $this->getChildHtml('search_button');
    }
}
