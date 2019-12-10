<?php

namespace BluestormDesign\TeamworkCrm\Models;

class Company extends Model
{
    protected function init()
    {
        $this->fields = [
            'name' => true,
            'addressLine1' => false,
            'addressLine2' => false,
            'city' => false,
            'stateOrCounty' => false,
            'zipcode' => false,
            'country' => false,
            'phoneNumbers' => false,
            'website' => false,
            'contacts' => false,
        ];
    }
}
