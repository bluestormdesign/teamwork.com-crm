<?php

namespace BluestormDesign\TeamworkCrm\Models;

class Pipeline extends Model
{
    protected function init()
    {
        $this->fields = [
            'name' => true,
            'isQualified' => false,
            'orderIndex' => false,
        ];
    }
}
