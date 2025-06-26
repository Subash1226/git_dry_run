<?php

namespace Baniwal\Recipes\Model;

use Baniwal\Recipes\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    const CACHE_TAG = 'health_package';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;
    const ID = 'id';
    const PACKAGE_NAME = 'package_name';
    const PRICE = 'price';
    const SPECIAL_PRICE = 'special_price';
    const DESCRIPTION = 'description';
    const SHORTDESCRIPTION = 'short_description';
    const IMPORTANCE = 'importance';
    const INCLUDED = 'included';
    const INCLUDEDTEST = 'includedtest';
    const AGE = 'age';
    const GENDER = 'gender';
    const BLOOD_GROUP = 'blood_group';
    const FASTING_REQUIRED = 'fasting_required';
    const CATEGORY = 'category';
    const IMAGE = 'image';

    protected function _construct()
    {
        $this->_init('Baniwal\Recipes\Model\ResourceModel\Grid');
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getPackageName()
    {
        return $this->getData(self::PACKAGE_NAME);
    }

    public function setPackageName($packageName)
    {
        return $this->setData(self::PACKAGE_NAME, $packageName);
    }

    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    public function getSpecialPrice()
    {
        return $this->getData(self::SPECIAL_PRICE);
    }

    public function setSpecialPrice($specialPrice)
    {
        return $this->setData(self::SPECIAL_PRICE, $specialPrice);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getShortDescription()
    {
        return $this->getData(self::SHORTDESCRIPTION);
    }

    public function setShortDescription($shortdescription)
    {
        return $this->setData(self::SHORTDESCRIPTION, $shortdescription);
    }

    public function getImportance()
    {
        return $this->getData(self::IMPORTANCE);
    }

    public function setImportance($importance)
    {
        return $this->setData(self::IMPORTANCE, $importance);
    }

    public function getAge()
    {
        return $this->getData(self::AGE);
    }

    public function setAge($age)
    {
        return $this->setData(self::AGE, $age);
    }

    public function getGender()
    {
        return $this->getData(self::GENDER);
    }

    public function setGender($gender)
    {
        return $this->setData(self::GENDER, $gender);
    }

    public function getCategory()
    {
        return $this->getData(self::CATEGORY);
    }

    public function setCategory($category)
    {
        return $this->setData(self::CATEGORY, $category);
    }

    public function getBloodGroup()
    {
        return $this->getData(self::BLOOD_GROUP);
    }

    public function setBloodGroup($bloodGroup)
    {
        return $this->setData(self::BLOOD_GROUP, $bloodGroup);
    }

    public function getFastingRequired()
    {
        return $this->getData(self::FASTING_REQUIRED);
    }

    public function setFastingRequired($fastingRequired)
    {
        return $this->setData(self::FASTING_REQUIRED, $fastingRequired);
    }

    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }
    public function getIncluded()
    {
        return $this->getData(self::INCLUDED);
    }

    public function setIncluded($included)
    {
        return $this->setData(self::INCLUDED, $included);
    }
    public function getIncludedTest()
    {
        return $this->getData(self::INCLUDEDTEST);
    }

    public function setIncludedTest($includedtest)
    {
        return $this->setData(self::INCLUDEDTEST, $includedtest);
    }
}
