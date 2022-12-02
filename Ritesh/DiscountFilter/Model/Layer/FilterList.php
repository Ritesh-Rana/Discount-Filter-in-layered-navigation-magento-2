<?php
//Ritesh

namespace Ritesh\DiscountFilter\Model\Layer;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer;
use Magento\Framework\ObjectManagerInterface;
/**
 * Class FilterList
 * @package Ritesh\DiscountFilter\Model\Layer
 */
class FilterList
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;
    protected $customFilters = null;

    /**
     * FilterList constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Layer $layer
     * @return FilterInterface[]
     */
    public function getCustomFilters($layer)
    {
        if(null === $this->customFilters) {
            $this->customFilters = [];
            $filter = $this->objectManager->create(
                Filter\Rating::class,
                ['layer' => $layer]
            );
            $this->customFilters[] = $filter;
        }
        return $this->customFilters;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param                                         $result
     * @param Layer                                   $layer
     */
    public function afterGetFilters(\Magento\Catalog\Model\Layer\FilterList $subject, $result, Layer $layer)
    {
        $customFilters = $this->getCustomFilters($layer);
        $result = array_merge($result, $customFilters);
        return $result;
    }
}
