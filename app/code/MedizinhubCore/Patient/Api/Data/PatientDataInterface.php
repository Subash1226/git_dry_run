<?php

namespace MedizinhubCore\Patient\Api\Data;

interface PatientDataInterface
{
    public function getName();

    public function setName($name);

    public function getEmail();

    public function setEmail($email);

    public function getAge();

    public function setAge($age);

    public function getHouseNo();

    public function setHouseNo($house_no);

    public function getStreet();

    public function setStreet($street);

    public function getCity();

    public function setCity($city);

    public function getArea();

    public function setArea($area);

    public function getRegionId();

    public function setRegionId($region_id);

    public function getCountryId();

    public function setCountryId($country_id);

    public function getGender();

    public function setGender($gender);

    public function getPostcode();

    public function setPostCode($postcode);

    public function getPhone();

    public function setPhone($phone);

    public function getWhatsApp();

    public function setWhatsApp($whatsapp);

    public function getBloodGroup();

    public function setBloodGroup($blood_group);

    public function getStatus();

    public function setStatus($status);

    public function getDateOfBirth();

    public function setDateOfBirth($date_of_birth);
}