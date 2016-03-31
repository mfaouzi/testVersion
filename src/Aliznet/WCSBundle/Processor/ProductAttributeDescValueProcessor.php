<?php

namespace Aliznet\WCSBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product Attribute Value Processor for descriptions attributes.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class ProductAttributeDescValueProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
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
     * @var string
     */
    protected $language;

    /**
     * @param Serializer              $serializer
     * @param ChannelManager          $channelManager
     * @param string[]                $mediaAttributeTypes
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(
        Serializer $serializer,
        ChannelManager $channelManager,
        array $mediaAttributeTypes,
        ProductBuilderInterface $productBuilder = null
    ) {
        $this->serializer = $serializer;
        $this->channelManager = $channelManager;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->productBuilder = $productBuilder;
    }

    /**
     * @param product $product
     *
     * @return array
     */
    public function process($product)
    {
        if (null !== $this->productBuilder) {
            $contextChannel = $this->channelManager->getChannelByCode($this->channel);
            $this->productBuilder->addMissingProductValues($product, [$contextChannel], $contextChannel->getLocales()->toArray());
        }

        $data['product'] = [];

        $i = 0;
        foreach ($product->getValues() as $value) {
            $product_name = $product->getValue('sku')->getProduct()->getLabel();
            $attr_code = '';
            $attr_value = '';

            $value->getAttribute()->setLocale($this->getLanguage());
            $parentGroup = $value->getAttribute()->getGroup()->getCode();
            $code = $value->getAttribute()->getCode();
            $attrValue = $product->getValue($code);
            if (!empty($code) && !empty($attrValue) && !empty($attrValue->__toString()) && $parentGroup === 'Descriptif') {
                $attr_code = $value->getAttribute()->getCode();
                $attr_value = $attrValue->__toString();
            }

            if (!empty($product_name) && !empty($attr_code) && !empty($attr_value)) {
                $data['product'][$i]['PartNumber'] = $product_name;
                $data['product'][$i]['attribute'] = $attr_code;
                $data['product'][$i]['values'] = $attr_value;
                $data['product'][$i]['delete'] = '0';
                ++$i;
            }
        }

        return $data['product'];
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
     * get language.
     *
     * @return string language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set exported categorie's language.
     *
     * @return string $language language
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
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
                $value->getAttribute()->getAttributeType(),
                $this->mediaAttributeTypes
            )) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
