<?php

/*
 * Entity Category
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */

namespace Aliznet\WCSBundle\Entity;

use Akeneo\Component\Classification\Model\Category as BaseCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/*
 * Category entity
 * @author    mfaouzi
 * @copyright ALIZNET (http://www.aliznet.fr/)
 * 
 */
class Category extends BaseCategory implements CategoryInterface {

    /** @var ArrayCollection of ProductInterface */
    protected $products;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var ArrayCollection of CategoryTranslation */
    protected $translations;

    /** @var ArrayCollection of Channel */
    protected $channels;

    /** @var \DateTime */
    protected $created;

    /** @var \Integer */
    protected $display;

    /** @var \String for small images location */
    protected $thumbnail;

    /** @var \String for full images location */
    protected $fullImage;

    public function __construct() {
        parent::__construct();

        $this->products = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->channels = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function hasProducts() {
        return $this->products->count() !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts() {
        return $this->products;
    }

    /**
     * Get products count
     *
     * @return int
     */
    public function getProductsCount() {
        return $this->products->count();
    }

    /**
     * Get created date
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale) {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation($locale = null) {
        $locale = ($locale) ? $locale : $this->locale;
        if (!$locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations() {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(AbstractTranslation $translation) {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(AbstractTranslation $translation) {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN() {
        return 'Aliznet\WCSBundle\Entity\CategoryTranslation';
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel() {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '[' . $this->getCode() . ']';
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return CategoryInterface
     */
    public function setLabel($label) {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Returns the channels linked to the category
     *
     * @return ArrayCollection
     */
    public function getChannels() {
        return $this->channels;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getReference() {
        return $this->code;
    }

    /**
     * Returns Integer
     */
    public function getDisplay() {
        return $this->display;
    }

     /**
     * Returns the Thumbnail location related to category
     *
     * @return String
     */
    public function getThumbnail() {
        return $this->thumbnail;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return CategoryInterface
     */
    public function setDisplay($display) {
        $this->display = $display;
        return $this;
    }

    /**
     * Set Thumbnail
     *
     * @param string $thumbnail
     *
     * @return CategoryInterface
     */
    public function setThumbnail($thumbnail) {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    /**
     * Returns the FullImage location related to category
     *
     * @return String
     */
    public function getFullImage() {
        return $this->fullImage;
    }

    /**
     * Set Full Image
     *
     * @param string $fullImage
     *
     * @return CategoryInterface
     */
    public function setFullImage($fullImage) {
        $this->fullImage = $fullImage;
    }

    public function getParentCode() {
        if (null !== parent::getParent()) {
            return parent::getParent()->getCode();
        }
    }

}
