<?php

namespace BluestormDesign\TeamworkCrm\Models;

class Contact extends Model
{
    protected function init()
    {
        $this->fields = [
            'title' => false,
            'firstName' => true,
            'lastName' => false,
            'addressLine1' => false,
            'addressLine2' => false,
            'city' => false,
            'stateOrCounty' => false,
            'zipcode' => false,
            'country' => false,
            'phoneNumbers' => false,
            'website' => false,
            'company' => false,
            'emailAddresses' => true,
        ];
    }
}
