<?php

namespace AcfBreeze\Package\Modules;

use AcfBreeze\Builder\Field;
use AcfBreeze\FieldsBuilder;

class SpacerModule extends Field
{
    /**
     * @var string
     */
    protected $title = 'Spacer Module';

    /**
     * @var array
     */
    protected $params = [
        'choices' => [
            ['small' => 'Small'],
            ['medium' => 'Medium'],
            ['large' => 'Large']
        ]
    ];

    /**
     * @param AcfBreeze\FieldsBuilder $builder
     * @return void
     */
    protected function builder(FieldsBuilder &$builder)
    {
        parent::builder($builder);

        $builder->addSelect('type', ['wrapper' => ['width' => 50]])
                ->addChoices($this->getParam('choices'));
    }
}
