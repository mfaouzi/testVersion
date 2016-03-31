<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product Processor.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var Serializer */
    protected $serializer;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Channel
     *
     * @var string Channel code
     */
    protected $channel;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var array Normalizer context */
    protected $normalizerContext;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param Serializer              $serializer
     * @param ChannelManager          $channelManager
     * @param string[]                $mediaAttributeTypes
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(
    Serializer $serializer, ChannelManager $channelManager, array $mediaAttributeTypes, ProductBuilderInterface $productBuilder = null
    ) {
        $this->serializer = $serializer;
        $this->channelManager = $channelManager;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->productBuilder = $productBuilder;
    }

    /**
     * @param type $product
     *
     * @return type
     */
    public function process($product)
    {
        if (null !== $this->productBuilder) {
            $contextChannel = $this->channelManager->getChannelByCode($this->channel);
            $this->productBuilder->addMissingProductValues($product, [$contextChannel], $contextChannel->getLocales()->toArray());
        }

        $groups = $product->getGroupCodes();
        $categories = $product->getCategoryCodes();

        $prices = $product->getValue('price')->getPrices();

        $i = 0;
        $data['product'] = [];
        $data['media'] = [];
        if (!empty($prices)) {
            $currencies = [];
            foreach ($prices as $price) {
                $currencies[] = $price->getCurrency();
            }
            foreach ($currencies as $currency) {
                $mediaValues = $this->getMediaProductValues($product);

                foreach ($mediaValues as $mediaValue) {
                    $data['media'][$i][] = $this->serializer->normalize(
                        $mediaValue->getMedia(), 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
                    );
                }

                $data['product'][$i]['PartNumber'] = $product->getValue('sku')->getProduct()->getLabel();
                $data['product'][$i]['Type'] = 'ITEM';
                $data['product'][$i]['ParentPartNumber'] = (empty($groups)) ? '' : $groups[0];
                $data['product'][$i]['Sequence'] = '1';
                $data['product'][$i]['ParentGroupIdentifier'] = (empty($categories)) ? '' : $categories[0];
                $data['product'][$i]['Currency'] = $currency;

                $filename = 'products_atrributes.txt';
                $dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
                $file = $dir.'/web/WCS/'.$filename;

                $filename = 'products_atrributes.txt';
                $dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
                $file = $dir.'/web/WCS/'.$filename;

                $attributes = [];
                if (file_exists($file) && file($file)) {
                    $lines = file($file);
                    foreach ($lines as $line) {
                        $att = str_replace(array("\r\n", "\n", "\r"), '', $line);
                        $attr = explode('=>', $att);
                        $csv_header = $attr[0];
                        $attribute_code = $attr[1];
                        $attributes[$csv_header] = $attribute_code;
                    }

                    foreach ($attributes as $code => $att) {
                        $values = $product->getValue($att, 'fr_FR', $this->getChannel());
                        if ($att == 'price') {
                            $values = $product->getValue($att)->getPrice($currency)->getData();
                        }
                        if ($att == 'ListPrice') {
                            $values = $product->getValue($att)->getPrice($currency)->getData();
                        }
                        $data['product'][$i][$code] = $values;
                    }
                }
                ++$i;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help',
                ],
            ],
        ];
    }

    /**
     * Set channel.
     *
     * @param string $channelCode Channel code
     *
     * @return $this
     */
    public function setChannel($channelCode)
    {
        $this->channel = $channelCode;

        return $this;
    }

    /**
     * Get channel.
     *
     * @return string Channel code
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get normalizer context.
     *
     * @return array $normalizerContext
     */
    protected function getNormalizerContext()
    {
        if (null === $this->normalizerContext) {
            $this->normalizerContext = [
                'scopeCode'   => $this->channel,
                'localeCodes' => $this->getLocaleCodes($this->channel),
            ];
        }

        return $this->normalizerContext;
    }

    /**
     * Get locale codes for a channel.
     *
     * @param string $channelCode
     *
     * @return array
     */
    protected function getLocaleCodes($channelCode)
    {
        $channel = $this->channelManager->getChannelByCode($channelCode);

        return $channel->getLocaleCodes();
    }

    /**
     * Fetch medias product values.
     *
     * @param ProductInterface $product
     *
     * @return ProductValueInterface[]
     */
    protected function getMediaProductValues(ProductInterface $product)
    {
        $values = [];
        foreach ($product->getValues() as $value) {
            if (in_array(
                            $value->getAttribute()->getAttributeType(), $this->mediaAttributeTypes
                    )) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
