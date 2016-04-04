<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;

/**
 * Attribute Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeProcessor extends ProcessorHelper implements ItemProcessorInterface
{
    /**
     * @var string
     */
    protected $wcsattributetype;

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
     * @param type $item
     *
     * @return array
     */
    public function process($item)
    {
        $result = [];
        $result['Identifier'] = $item->getCode();
        $result['type'] = $this->processattributeType($item->getAttributeType());
        $result['label_'.$this->getLanguage()] = $item->setLocale($this->getLanguage())->getLabel();
        $result['Sequence'] = 1;
        $result['Displayable'] = 'True';
        $result['Searchable'] = ($item->isUseableAsGridFilter()) ? 'True' : 'False';
        $result['Comparable'] = '';
        $result['Delete'] = '';

        return $result;
    }

    /**
     * Get exportedAttributes.
     *
     * @param string $attributetype attributetype
     *
     * @return string $attributetype attributetype
     */
    public function processattributeType($attributetype)
    {
        $pimAttributeTypeInteger = array('pim_catalog_boolean', 'pim_catalog_number');
        $pimAttributeTypeFloat = array('pim_catalog_metric');
        $pimAttributeTypeDouble = array('pim_catalog_price_collection');
        $pimAttributeTypeString = array('pim_catalog_date', 'pim_catalog_file', 'pim_catalog_identifier', 'pim_catalog_image', 'pim_catalog_multiselect',
                                          'pim_catalog_simpleselect', 'pim_catalog_text', 'pim_catalog_textarea', );
        if (in_array($attributetype, $pimAttributeTypeInteger)) {
            $this->wcsattributetype = 'integer';
        } elseif (in_array($attributetype, $pimAttributeTypeFloat)) {
            $this->wcsattributetype = 'float';
        } elseif (in_array($attributetype, $pimAttributeTypeDouble)) {
            $this->wcsattributetype = 'double';
        } elseif (in_array($attributetype, $pimAttributeTypeString)) {
            $this->wcsattributetype = 'string';
        } else {
            $this->wcsattributetype = $attributetype;
        }

        return $this->wcsattributetype;
    }
}
