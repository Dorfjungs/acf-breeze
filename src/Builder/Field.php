<?php

namespace AcfBreeze\Builder;

use AcfBreeze\Exceptions\FieldNameNotDefinedException;
use AcfBreeze\Exceptions\ParameterNotFoundException;
use AcfBreeze\FieldsBuilder;

abstract class Field
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var AcfBreeze\FieldsBuilder
     */
    protected $builder = null;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param string $name
     * @param array $params
     */
    public function __construct($name, $params = [])
    {
        $this->name = $name;
        $this->params = array_replace($this->params, $params);

        if (empty($this->name)) {
            throw new FieldNameNotDefinedException(
                'You need to define a name for the group (as property)'
            );
        }

        $this->builder = new FieldsBuilder(
            $this->name($this->name),
            [ 'title' => $this->title($this->title) ]
        );

        $this->builder($this->builder);
        $this->config($this->config);

        foreach ($this->config as $key => $value) {
            $this->builder->setGroupConfig($key, $value);
        }
    }

    /**
     * @param AcfBreeze\FieldsBuilder $builder
     * @return void
     */
    protected function builder(FieldsBuilder &$builder)
    {
    }

    /**
     * @param array &$config
     * @return void
     */
    protected function config(&$config)
    {
    }

    /**
     * @param string $name
     * @return string
     */
    protected function name($name)
    {
        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function title($title)
    {
        return $title;
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function getParam($name)
    {
        if ( ! array_key_exists($name, $this->params)) {
            throw new ParameterNotFoundException(
                sprintf('Paramter "%s" was not found for "%s"', $name, $this->name)
            );
        }

        return $this->params[$name];
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasParam($name)
    {
        return array_key_exists($name, $this->params);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return FieldsBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return FieldsBuilder
     */
    public function build()
    {
        return $this->builder->build();
    }
}
