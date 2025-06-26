<?php
namespace Baniwal\Recipes\Api\Data;

interface GridInterface
{
    const ID = 'id';
    const PACKAGE_NAME = 'package_name';
    const PRICE = 'price';
    const SPECIAL_PRICE = 'special_price';
    const DESCRIPTION = 'description';
    const CATEGORY = 'category';
    const SHORTDESCRIPTION = 'shortdescription';
    const IMPORTANCE = 'importance';
    const INCLUDED = 'included';
    const INCULDEDTEST = 'includedtest';
    const AGE = 'age';
    const GENDER = 'gender';
    const BLOOD_GROUP = 'blood_group';
    const FASTING_REQUIRED = 'fasting_required';
    const IMAGE = 'image';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Package Name
     *
     * @return string|null
     */
    public function getPackageName();

    /**
     * Set Package Name
     *
     * @param string $packageName
     * @return $this
     */
    public function setPackageName($packageName);

    /**
     * Get Price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set Price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get Special Price
     *
     * @return float|null
     */
    public function getSpecialPrice();

    /**
     * Set Special Price
     *
     * @param float $specialPrice
     * @return $this
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set Description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get Importance
     *
     * @return string|null
     */
    public function getImportance();

    /**
     * Set Importance
     *
     * @param string $importance
     * @return $this
     */
    public function setImportance($importance);

    /**
     * Get Age
     *
     * @return int|null
     */
    public function getAge();

    /**
     * Set Included
     *
     * @param string $included
     * @return $this
     */
    public function setIncluded($included);


    /**
     * Get Included
     *
     * @return string|null
     */
    public function getIncluded();

    /**
     * Set includedtest
     *
     * @param string $includedtest
     * @return $this
     */
    public function setIncludedTest($includedtest);


    /**
     * Get includedtest
     *
     * @return string|null
     */
    public function getIncludedTest();

    /**
     * Set Age
     *
     * @param int $age
     * @return $this
     */
    public function setAge($age);

    /**
     * Get Gender
     *
     * @return string|null
     */
    public function getGender();

    /**
     * Set Gender
     *
     * @param string $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get Category
     *
     * @return string|null
     */
    public function getCategory();

    /**
     * Set Category
     *
     * @param string $category
     * @return $this
     */
    public function setCategory($category);

    /**
     * Get Blood Group
     *
     * @return string|null
     */
    public function getBloodGroup();

    /**
     * Set Blood Group
     *
     * @param string $bloodGroup
     * @return $this
     */
    public function setBloodGroup($bloodGroup);

    /**
     * Get Fasting Required
     *
     * @return int|null
     */
    public function getFastingRequired();

    /**
     * Set Fasting Required
     *
     * @param int $fastingRequired
     * @return $this
     */
    public function setFastingRequired($fastingRequired);

    /**
     * Get Image
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Set Image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * Get ShortDescription
     *
     * @return string|null
     */
    public function getShortDescription();

    /**
     * Set ShortDescription
     *
     * @param string $shortdescription
     * @return $this
     */
    public function setShortDescription($shortdescription);
}
