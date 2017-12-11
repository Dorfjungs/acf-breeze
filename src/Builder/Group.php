<?php

namespace AcfBreeze\Builder;

use AcfBreeze\FieldsBuilder;
use StoutLogic\AcfBuilder\FlexibleContentBuilder;

abstract class Group extends Field
{
    /**
     * @var FlexibleContentBuilder
     */
    protected $flexContent = null;

    /**
     * @param AcfBreeze\FieldsBuilder $field
     * @return void
     */
    protected function builder(FieldsBuilder &$builder)
    {
        if ($this->hasParam('locations')) {
            $locations = $this->getParam('locations');

            if ( ! empty($locations) && is_array($locations)) {
                $fnc = 'setLocation';
                $lastBuilder = $builder;

                foreach ($locations as $location) {
                    $type = $location[0];
                    $operator = $location[1];
                    $value = $location[2];

                    if ($type && $operator && $value) {
                        $lastBuilder = $lastBuilder->{$fnc}($type, $operator, $value);

                        $fnc = array_key_exists(3, $location)
                                ? $location[3]
                                : 'or';
                    }
                }
            }
        }
    }

    /**
     * @param FlexibleContentBuilder &$field
     * @param Layout $layout
     */
    public function addLayout(Layout $layout)
    {
        if (is_null($this->flexContent) && $this->builder) {
            $this->flexContent = $this->builder->addFlexibleContent(
                $this->name . '_flex_content',
                [ 'label' => 'Layout' ]
            );
        }

        $this->flexContent->addLayout($layout->getBuilder());
    }
}
