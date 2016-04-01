<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Product Attribute Value Processor for descriptions attributes.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductAttributeDescValueProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @param ChannelManager          $channelManager
     * @param string[]                $mediaAttributeTypes
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(
        ChannelManager $channelManager,
        array $mediaAttributeTypes,
        ProductBuilderInterface $productBuilder = null
    ) {
        parent::__construct($channelManager, $mediaAttributeTypes, $productBuilder);
    }

    /**
     * @param product $product
     *
     * @return array
     */
    public function process($product)
    {
        parent::process($product);
        $data['product'] = [];

        return $this->fillProductData($product, $data);
    }

    protected function fillProductData($product, $data)
    {
        $i = 0;
        foreach ($this->getAttributes($product) as $attr) {
            $productName = $product->getValue('sku', $this->getLanguage(), $this->getChannel())->getProduct()->getLabel();
            $parentGroup = $attr->getGroup()->getCode();
            $code = $attr->getCode();
            $attrValue = $product->getValue($code, $this->getLanguage(), $this->getChannel());
            if (!empty($code) && !empty($attrValue) && !empty($attrValue->__toString()) && $parentGroup === 'Descriptif') {
                if (!empty($productName) && !empty($code) && !empty($attrValue)) {
                    $data['product'][$i]['PartNumber'] = $productName;
                    $data['product'][$i]['attribute'] = $code;
                    $data['product'][$i]['values'] = $attrValue->__toString();
                    $data['product'][$i]['delete'] = '0';
                    ++$i;
                }
            }
        }

        return $data['product'];
    }

    /**
     * @param type $product
     *
     * @return type
     */
    protected function getAttributes($product)
    {
        $attributes = [];

        foreach ($product->getValues() as $value) {
            if (!in_array($value->getAttribute(), $attributes)) {
                $attributes[] = $value->getAttribute();
            }
        }

        return $attributes;
    }
}
