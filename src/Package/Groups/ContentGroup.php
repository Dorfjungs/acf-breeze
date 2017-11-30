<?php

namespace AcfBreeze\Package\Groups;

use AcfBreeze\Builder\Group;
use AcfBreeze\FieldsBuilder;

class ContentGroup extends Group
{
    /**
     * @var string
     */
    protected $title = 'Content';

    /**
     * {@inheritDoc}
     */
    public function builder(FieldsBuilder &$builder)
    {
        parent::builder($builder);

        $builder->setGroupConfig('hide_on_screen', ['the_content']);
        $builder->setLocation('page_template', '==', 'default')
              ->or('post_type', '==', 'post');
    }
}
