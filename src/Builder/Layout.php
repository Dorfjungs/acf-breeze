<?php

namespace AcfBreeze\Builder;

use AcfBreeze\FieldsBuilder;

abstract class Layout extends Field
{
    /**
     * {@inheritDoc}
     */
    protected function builder(FieldsBuilder &$builder)
    {
    }

    /**
     * @param Field $builder
     * @return void
     */
    public function addModule(Field &$builder)
    {
    }
}
