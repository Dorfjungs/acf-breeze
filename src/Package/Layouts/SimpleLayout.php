<?php

namespace AcfBreeze\Package\Layouts;

use AcfBreeze\Builder\Field;
use AcfBreeze\FieldsBuilder;
use AcfBreeze\Builder\Layout;
use StoutLogic\AcfBuilder\FieldsBuilder as StoutLogicFieldsBuilder;

class SimpleLayout extends Layout
{
    /**
     * @var string
     */
    protected $title = 'Simple Layout';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    private $flexModuleContents = [];

    /**
     * {@inheritDoc}
     */
    protected function builder(FieldsBuilder &$builder)
    {
        parent::builder($builder);

        $this->addGroups($builder);
    }

    /**
     * @param FieldsBuilder $builder
     */
    protected function addGroups(FieldsBuilder &$builder)
    {
        $this->addContent($builder);
    }

    /**
     * @param Field $builder
     * @return void
     */
    public function addModule(Field &$builder)
    {
        foreach ($this->flexModuleContents as $flexContent) {
            $flexContent->addLayout($builder->getBuilder());
        }
    }

    /**
     * @param StoutLogicFieldsBuilder $field
     * @return void
     */
    protected function addContent(StoutLogicFieldsBuilder &$field)
    {
        $tabContent = $field->addTab('Content');
        $flexContent = $tabContent->addFlexibleContent('modules_content', [
            'label' => __('Modules')
        ]);

        $this->flexModuleContents[] = $flexContent;
    }
}
