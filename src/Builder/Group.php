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
        $this->flexContent = $builder->addFlexibleContent(
            $this->name . '_flex_content',
            [ 'label' => 'Layout' ]
        );
    }

    /**
     * @param FlexibleContentBuilder &$field
     * @param Layout $layout
     */
    public function addLayout(Layout $layout)
    {
        $this->flexContent->addLayout($layout->getBuilder());
    }
}
