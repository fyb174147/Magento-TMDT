<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\AddressInterface;

class Address implements AddressInterface
{
    use DataObjectTrait;

    public function getFirstname() { return (string)$this->getValue('firstname', ''); }
    public function setFirstname($firstname) { return $this->setValue('firstname', (string)$firstname); }
    public function getLastname() { return (string)$this->getValue('lastname', ''); }
    public function setLastname($lastname) { return $this->setValue('lastname', (string)$lastname); }
    public function getStreet() { return $this->getValue('street', []); }
    public function setStreet($street) { return $this->setValue('street', is_array($street) ? $street : [(string)$street]); }
    public function getCity() { return (string)$this->getValue('city', ''); }
    public function setCity($city) { return $this->setValue('city', (string)$city); }
    public function getRegion() { return (string)$this->getValue('region', ''); }
    public function setRegion($region) { return $this->setValue('region', (string)$region); }
    public function getPostcode() { return (string)$this->getValue('postcode', ''); }
    public function setPostcode($postcode) { return $this->setValue('postcode', (string)$postcode); }
    public function getCountryId() { return (string)$this->getValue('country_id', ''); }
    public function setCountryId($countryId) { return $this->setValue('country_id', (string)$countryId); }
    public function getTelephone() { return (string)$this->getValue('telephone', ''); }
    public function setTelephone($telephone) { return $this->setValue('telephone', (string)$telephone); }
}
