<?php

namespace Magefox\GoogleShopping\Helper;

class Products extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Eav\ModelAttributeSetRepository
     */
    protected $_attributeSetRepo;

    /**
     * @var \Magefox\GoogleShopping\Helper\Data
     */
    protected $_helper;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    public $_storeManager;

    /**
    * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
    */
    public $_productStatus;

    /**
    * @var \Magento\Catalog\Model\Product\Visibility
    */
    public $_productVisibility;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Model\AttributeSetRepository $attributeSetRepo,
        \Magefox\GoogleShopping\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    )
    {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_attributeSetRepo = $attributeSetRepo;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        parent::__construct($context);
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    public function getFilteredProducts()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        // $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());

        return $collection;
    }

    public function getAttributeSet($product)
    {
        $attributeSetId = $product->getAttributeSetId();
        $attributeSet = $this->_attributeSetRepo->get($attributeSetId);

        return $attributeSet->getAttributeSetName();

    }

    public function getProductValue($product, $attributeCode)
    {
        $attributeCodeFromConfig = $this->_helper->getConfig($attributeCode.'_attribute');
        $defaultValue = $this->_helper->getConfig('default_'.$attributeCode);

        if (!empty($attributeCodeFromConfig))
        {
            return $product->getAttributeText($attributeCodeFromConfig);
        }

        if (!empty($defaultValue))
        {
            return $defaultValue;
        }

        return false;
    }

    public function getCategoryNames($product)
    {
//        $categoryCollection = clone $product->getCategoryCollection();
//        $categoryCollection->clear();
//
//        $categoryCollection->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC);
//        $categoryCollection->setPageSize(1);
//
//        $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();
//

//        $categoryIds = $product->getCategoryIds();
//        $categories  = $this->getCategoryCollection()->addAttributeToFilter('entity_id', $categoryIds);
//        $path        = '';
//        $i           = 0;
//
//        foreach ($categories as $category) {
//            if (0 === $i) {
//                $path .= 'Startseite > ';
//            }
//            if ($i > 0) {
//                $path .= ' > ';
//            }
//            $path .= $category->getName();
//            $i++;
//        }

        $categoryIds = $product->getCategoryIds();
        $category    = $this->getCategoryCollection()->addAttributeToFilter('entity_id', end($categoryIds))->getFirstItem();

        return $category->getName();
    }

    public function getCurrentCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
}
