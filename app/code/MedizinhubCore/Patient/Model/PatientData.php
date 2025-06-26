<?php

namespace MedizinhubCore\Patient\Model;

use MedizinhubCore\Patient\Api\Data\PatientDataInterface;

class PatientData implements PatientDataInterface
{
    protected $name;
    protected $email;
    protected $age;
    protected $house_no;
    protected $street;
    protected $city;
    protected $area;
    protected $region_id;
    protected $country_id;
    protected $gender;
    protected $postcode;
    protected $phone;
    protected $whatsapp;
    protected $blood_group;
    protected $status;
    protected $date_of_birth;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        $this->age = $age;
    }

    public function getHouseNo()
    {
        return $this->house_no;
    }

    public function setHouseNo($house_no)
    {
        $this->house_no = $house_no;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getArea()
    {
        return $this->area;
    }

    public function setArea($area)
    {
        $this->area = $area;
    }

    public function getRegionId()
    {
        return $this->region_id;
    }

    public function setRegionId($region_id)
    {
        $this->region_id = $region_id;
    }

    public function getCountryId()
    {
        return $this->country_id;
    }

    public function setCountryId($country_id)
    {
        $this->country_id = $country_id;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function setPostCode($postcode)
    {
        $this->postcode = $postcode;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getWhatsApp()
    {
        return $this->whatsapp;
    }

    public function setWhatsApp($whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function getBloodGroup()
    {
        return $this->blood_group;
    }

    public function setBloodGroup($blood_group)
    {
        $this->blood_group = $blood_group;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    public function setDateOfBirth($date_of_birth)
    {
        $this->date_of_birth = $date_of_birth;
    }
}