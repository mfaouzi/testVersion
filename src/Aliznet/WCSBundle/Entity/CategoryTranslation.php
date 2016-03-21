<?php

namespace Aliznet\WCSBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation as BaseCategoryTranslation;

/*
 * Category translation entity
 * @author    aliznet
 * @copyright ALIZNET (http://www.aliznet.fr/)
 * 
 */

class CategoryTranslation extends BaseCategoryTranslation {
    /**
     * All required columns are mapped through inherited superclass
     */
   
    protected $name;
    protected $description;
    protected $longDescription;
    protected $keyword;
    
    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getLongDescription() {
        return $this->longDescription;
    }

    public function getKeyword() {
        return $this->keyword;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setLongDescription($longDescription) {
        $this->longDescription = $longDescription;
        return $this;
    }

    public function setKeyword($keyword) {
        $this->keyword = $keyword;
        return $this;
    }
}
