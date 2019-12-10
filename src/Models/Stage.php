<?php

namespace BluestormDesign\TeamworkCrm\Models;

class Stage extends Model
{
    protected function init()
    {
        $this->fields = [
            'name' => true,
            'orderIndex' => false,
            'pipeline' => false,
            'probability' => false,
        ];
    }
}
