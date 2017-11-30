<?php

namespace AcfBreeze\Models;

use AcfBreeze\Adapter\AcfAdapter;

class PackageModel
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var array
     */
    public $groups = [];

    /**
     * @var array
     */
    public $layouts = [];

    /**
     * @var array
     */
    public $modules = [];

    /**
     * @var array
     */
    public $paths = [];

    /**
     * @var AcfAdapter
     */
    public $adapter = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->adapter = new AcfAdapter($this);
    }
}
