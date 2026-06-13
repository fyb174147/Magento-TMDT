<?php

namespace Bookstore\CoreApi\Api\Data;

interface AddressInterface
{
    /** @return string */
    public function getFirstname();
    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);
    /** @return string */
    public function getLastname();
    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);
    /** @return string[] */
    public function getStreet();
    /**
     * @param string[] $street
     * @return $this
     */
    public function setStreet($street);
    /** @return string */
    public function getCity();
    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city);
    /** @return string */
    public function getRegion();
    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region);
    /** @return string */
    public function getPostcode();
    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);
    /** @return string */
    public function getCountryId();
    /**
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);
    /** @return string */
    public function getTelephone();
    /**
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);
}
