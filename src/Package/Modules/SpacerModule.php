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
     * @param AcfBreeze\FieldsBuilder $builder
     * @return void
     */
    protected function builder(FieldsBuilder &$builder)
    {
        parent::builder($builder);

        $choices = [
            ['small' => 'Small'],
            ['medium' => 'Medium'],
            ['large' => 'Large']
        ];

        $builder->addSelect('type', ['wrapper' => ['width' => 50]])
                ->addChoices($choices)
                ->addTrueFalse('with_border', ['wrapper' => ['width' => 50]]);
    }
}
