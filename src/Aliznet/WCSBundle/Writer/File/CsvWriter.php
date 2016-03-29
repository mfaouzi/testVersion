<?php

namespace Aliznet\WCSBundle\Writer\File;

use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CSV Product Writer.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class CsvProductWriter extends CsvWriter
{
    /**
     * @var string
     */
    protected $exportPriceOnly;

    /**
     * Assert\NotBlank(groups={"Execution"})
     * Channel.
     *
     * @var string Channel code
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var array
     */
    protected $fixedDatas = array('family', 'groups', 'categories', 'RELATED-groups', 'RELATED-products');

    /**
     * @param MediaManager $mediaManager
     */
    public function __construct($entityManager, ChannelManager $channelManager)
    {
        $this->entityManager = $entityManager;
        $this->channelManager = $channelManager;
    }

    /**
     * Set the configured channel.
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the configured channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * get exportPriceOnly.
     *
     * @return string exportPriceOnly
     */
    public function getExportPriceOnly()
    {
        return $this->exportPriceOnly;
    }

    /**
     * Set exportPriceOnly.
     *
     * @param string $exportPriceOnly exportPriceOnly
     *
     * @return AbstractProcessor
     */
    public function setExportPriceOnly($exportPriceOnly)
    {
        $this->exportPriceOnly = $exportPriceOnly;

        return $this;
    }

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        $products = [];

        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

        foreach ($items as $item) {
            $item['product'] = $this->getProductPricesOnly($item['product']);
            $item['product'] = $this->formatMetricsColumns($item['product']);
            //$products[] = ($this->getExportImages()) ? $item['product'] : $this->removeMediaColumns($item['product']);
            $products[] = $item['product'];
        }

        $this->items = array_merge($this->items, $products);
    }

    /**
     * @param array|AbstractProductMedia $media
     */
    public function sendMedia($media)
    {
        $filePath = null;
        $exportPath = null;

        if (is_array($media)) {
            $filePath = $media['filePath'];
            $exportPath = $media['exportPath'];
        } else {
            if ('' !== $media->getFileName()) {
                $filePath = $media->getFilePath();
            }
            $exportPath = $this->mediaManager->getExportPath($media);
        }

        if (null === $filePath) {
            return;
        }

        $dirname = dirname($exportPath);
    }

    /**
     * @param $item array
     * Get only prices or all data without prices
     *
     * @return array
     */
    protected function getProductPricesOnly($item)
    {
        if ($this->getExportPriceOnly() == 'all') {
            return $item;
        }
        $attributeEntity = $this->entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $attributes = $attributeEntity->getNonIdentifierAttributes();
        foreach ($attributes as $attribute) {
            if ($this->getExportPriceOnly() == 'onlyPrices') {
                if ($attribute->getBackendType() != 'prices') {
                    $attributesToRemove = preg_grep('/^'.$attribute->getCode().'D*/', array_keys($item));
                    foreach ($attributesToRemove as $attributeToRemove) {
                        unset($item[$attributeToRemove]);
                    }
                }
            } elseif ($this->getExportPriceOnly() == 'withoutPrices') {
                if ($attribute->getBackendType() == 'prices') {
                    $attributesToRemove = preg_grep('/^'.$attribute->getCode().'D*/', array_keys($item));
                    foreach ($attributesToRemove as $attributeToRemove) {
                        unset($item[$attributeToRemove]);
                    }
                }
            }
        }

        if ($this->getExportPriceOnly() == 'onlyPrices') {
            foreach ($this->fixedDatas as $fixedData) {
                unset($item[$fixedData]);
            }
        }

        return $item;
    }

    /**
     * @param array $item
     *                    Add channel code to metric attributes header columns
     *
     * @return array
     */
    protected function formatMetricsColumns($item)
    {
        $attributeEntity = $this->entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $attributes = $attributeEntity->getNonIdentifierAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getBackendType() == 'metric') {
                if (array_key_exists($attribute->getCode(), $item)) {
                    $item[$attribute->getCode().'-'.$this->getChannel()] = $item[$attribute->getCode()];
                    unset($item[$attribute->getCode()]);
                }
            }
        }

        return $item;
    }

    /**
     * @param array $item
     *                    Remove all column of attributes with type media
     *
     * @return array
     */
    protected function removeMediaColumns($item)
    {
        $attributeEntity = $this->entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $mediaAttributesCodes = $attributeEntity->findMediaAttributeCodes();
        foreach ($mediaAttributesCodes as $mediaAttributesCode) {
            if (array_key_exists($mediaAttributesCode, $item)) {
                unset($item[$mediaAttributesCode]);
            }
        }

        return $item;
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return
                array_merge(
                array(
            'exportPriceOnly' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices' => array(
                        'all'           => 'aliznet_wcs_export.export.exportPriceOnly.choices.all',
                        'withoutPrices' => 'aliznet_wcs_export.export.exportPriceOnly.choices.withoutPrices',
                        'onlyPrices'    => 'aliznet_wcs_export.export.exportPriceOnly.choices.onlyPrices',
                    ),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'aliznet_wcs_export.export.exportPriceOnly.label',
                    'help'     => 'aliznet_wcs_export.export.exportPriceOnly.help',
                ),
            ),
                ), parent::getConfigurationFields()
        );
    }
	
	/**
     * Get a set of all keys inside arrays.
     *
     * @param array $items
     *
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $intKeys = [];
        foreach ($items as $itemss) {
            foreach ($itemss as $item) {
                $intKeys[] = array_keys($item);
            }
        }
        if (0 === count($intKeys)) {
            return [];
        }
        $mergedKeys = call_user_func_array('array_merge', $intKeys);

        return array_unique($mergedKeys);
    }

    /**
     * Merge the keys in arrays.
     *
     * @param array $uniqueKeys
     *
     * @return array
     */
    protected function mergeKeys($uniqueKeys)
    {
        $uniqueKeys = array_fill_keys($uniqueKeys, '');
        $fullItems = [];
        foreach ($this->items as $itemss) {
            foreach ($itemss as $item) {
                $fullItems[] = array_merge($uniqueKeys, $item);
            }
        }

        return $fullItems;
    }
}
