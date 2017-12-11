<?php

namespace AcfBreeze\Models\Package;

use AcfBreeze\Adapter\AcfAdapter;

abstract class AbstractModel
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $class = '';

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var string
     */
    public $package = '';

    /**
     * @var string
     */
    public $path = '';

    /**
     * @var string
     */
    public $entry = '';

    /**
     * @var string
     */
    public $fileExt = 'twig';

    /**
     * @var AcfAdapter
     */
    public $adapter = null;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var self
     */
    public $parent = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->adapter = new AcfAdapter();
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->package . '/' . $this->path . '/' . $this->name . '.' . $this->fileExt;
    }

    /**
     * @return string
     */
    protected function getEntryName()
    {
        return $this->name . (empty($this->entry) ? '' : ('_' . $this->entry));
    }

    /**
     * @param array $fieldNames
     * @return void
     */
    public function classes(...$fieldNames)
    {
        return $this->adapter->getClassesFromData($fieldNames, $this->data());
    }

    /**
     * @param number $id
     * @return array
     */
    protected function data($id = -1)
    {
        return ! empty($this->data)
            ? $this->data
            : $this->adapter->getField($this->getEntryName(), $id);
    }
}
