<?php

namespace AcfBreeze;

class FieldsBuilder extends \StoutLogic\AcfBuilder\FieldsBuilder
{
    /**
     * @param string $name
     * @param array $groupConfig
     */
    public function __construct($name = '', array $groupConfig = [])
    {
        parent::__construct($name, $groupConfig);
    }

    /**
     * @param [type] $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
