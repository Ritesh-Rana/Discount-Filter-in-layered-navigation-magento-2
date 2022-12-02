<?php

//Ritesh

namespace Ritesh\DiscountFilter\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Rating
 * @package Ritesh\DiscountFilter\Model\Layer\Filter
 */
class Rating extends AbstractFilter
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $productCollectionFactory;
    /**
     * @var PriceFactory
     */
    private $dataProvider;
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        CollectionFactory $productCollectionFactory,
        PriceFactory $dataProviderFactory,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_requestVar = 'discount';
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @param RequestInterface $request
     * @return $this|AbstractFilter
     */
    public function apply(RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }
        $totalproduct = 0;
        $filter = explode('-', $filter);
        if(count($filter)==2)
        list($from, $to) = $filter;
        else{$from=1;$to=100;}
        $this->getLayer()->getState()->addFilter($this->_createItem($from . "% - " . $to . "%", 0));
        $entity_id = [];
        $collection = $this->getLayer()->getCurrentCategory()
            ->getProductCollection()
            ->addAttributeToSelect(array('sku', 'price', 'special_price'))
            ->addAttributeToFilter('special_price', ['neq' => NULL]);
        foreach ($collection as $product) {
            $totalproduct++;
            $price  = $product->getPrice();

            $sprice = $product->getSpecialPrice();
            if ($price > 0) {
                $dis = ($price - $sprice) * 100 / $price;
                $dis = (int)$dis;

                if ($dis >= (int)$from && $dis <= (int)$to) {
                    $entity_id[] = $product->getId();
                }
            }
        }
        $this->getLayer()
            ->getProductCollection()
            ->addAttributeToFilter('entity_id', array('in' => ($entity_id)));
        return $this;
    }

    /**
     * @return Phrase
     */
    public function getName(): Phrase
    {
        return __('Discount');
    }

    /**
     * @return array
     */
    protected function _getItemsData(): array
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();;
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $facets = array();
        if ($scopeConfig->getValue('discountFiltered/discountFilterGroup/isEnableDisable')) {
            $facets = array(
                '1-10',
                '11-20',
                '21-30',
                '31-40',
                '41-50',
                '51-60',
                '61-70',
                '71-80',
                '81-90',
                '91-100',
            );
            if (count($facets) >= 1) {
                foreach ($facets as $key) {
                    $filter = explode('-', $key);
                    list($from, $to) = $filter;
                    $collection = $this->getLayer()->getCurrentCategory()
                        ->getProductCollection()
                        ->addAttributeToSelect(array('sku', 'price', 'special_price'))
                        ->addAttributeToFilter('special_price', ['neq' => NULL]);

                    $count1 = 0;
                    foreach ($collection as $product) {
                        $price  = $product->getPrice();
                        $sprice = $product->getSpecialPrice();
                        if ($sprice > 0) {
                            $dis = (($price - $sprice) * 100) / $price;
                            $dis = (int)$dis;
                            if ($dis >= (int)$from && $dis <= (int)$to) {
                                $count1++;
                            }
                        }
                    }

                    if ($count1 >= 0) {
                        $this->itemDataBuilder->addItemData(
                            $from . "% - " . $to . "%",
                            $key,
                            $count1
                        );
                    }
                }
            }
            return $this->itemDataBuilder->build();
        }
        die;
    }
}
