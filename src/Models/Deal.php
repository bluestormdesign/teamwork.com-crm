<?php

namespace BluestormDesign\TeamworkCrm\Models;

class Deal extends Model
{
    protected function init()
    {
        $this->fields = [
            'title' => true,
            'company' => false,
            'teamworkProjects' => false,
            'closedAt' => false,
            'expectedCloseDate' => false,
            'stage' => true,
            'currency' => true,
        ];
    }
}
