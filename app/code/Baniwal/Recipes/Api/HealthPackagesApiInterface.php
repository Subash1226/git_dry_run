<?php
namespace Baniwal\Recipes\Api;

interface HealthPackagesApiInterface
{
    /**
     * Get all health packages
     *
     * @return \MedizinhubCore\Lab\Api\Data\LabResponseInterface
     */
    public function getAllHealthPackages();

    /**
     * Get package by id
     *
     * @param string $id
     * @return Data\GridInterface
     */
    public function getPackageByName($id);

    /**
     * Get package by category
     *
     * @param string $category
     * @return \MedizinhubCore\Lab\Api\Data\LabResponseInterface
     */
    public function getPackageByCategory($category);
}