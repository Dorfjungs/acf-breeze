<?php

namespace AcfBreeze\Package\Layouts;

use AcfBreeze\Builder\Field;
use AcfBreeze\Builder\Layout;
use AcfBreeze\FieldsBuilder;

class ColumnLayout extends Layout
{
    /**
     * @var string
     */
    protected $title = 'Column Layout';

    /**
     * @var array
     */
    protected $params = [
        'columns' => 6,
        'skipColumns' => [ 5 ],
        'breakpoints' => [ 'xs', 'sm', 'md', 'lg' ],
        'baseWidth' => 12,
        'columnSizes' => [
            2 => [
                ['1/1', '1/1'],
                ['1/2', '1/2'],
                ['1/4', '3/4'],
                ['3/4', '1/4'],
                ['4/6', '2/6'],
                ['2/6', '4/6']
            ],
            3 => [
                ['1/1', '1/1', '1/1'],
                ['1/3', '1/3', '1/3'],
                ['1/4', '2/4', '1/4'],
                ['1/6', '4/6', '1/6']
            ],
            4 => [
                ['1/1', '1/1', '1/1', '1/1'],
                ['1/4', '1/4', '1/4', '1/4'],
                ['1/2', '1/2', '1/2', '1/2']
            ],
            6 => [
                ['1/1', '1/1', '1/1', '1/1', '1/1', '1/1'],
                ['1/6', '1/6', '1/6', '1/6', '1/6', '1/6'],
                ['1/3', '1/3', '1/3', '1/3', '1/3', '1/3'],
                ['1/2', '1/2', '1/2', '1/2', '1/2', '1/2']
            ]
        ]
    ];

    /**
     * @var array
     */
    protected $layoutModes = [
        ['fluid' => 'Fluid'],
        ['wrapper' => 'Maxed']
    ];

    /**
     * @var array
     */
    protected $verticalAlignments = [
        ['' => 'None'],
        ['top' => 'Top'],
        ['middle' => 'Middle'],
        ['bottom' => 'Bottom']
    ];

    /**
     * @var array
     */
    protected $horizontalAlignments = [
        ['' => 'None'],
        ['start' => 'Start'],
        ['center' => 'Center'],
        ['end' => 'End']
    ];

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

        $baseWidth = $this->getParam('baseWidth');
        $skipColums = $this->getParam('skipColumns');
        $columnSizes = $this->getParam('columnSizes');
        $columns = $this->getParam('columns');

        // Add column selection
        $columnChoices = [];

        for ($i = 0; $i < $columns; $i++) {
            $currentCol = ($i + 1);

            if (in_array($currentCol, $skipColums)) {
                continue;
            }

            $columnSuffix = ' Column' . (($currentCol > 1) ? 's' : '');
            $columnChoices[] = [$currentCol => $currentCol . $columnSuffix];
        }

        $builder->addSelect('columns', ['label' => ''])
                ->addChoices($columnChoices);

        // Add general column settings
        $this->addSettings($builder);

        // Create tabs and add modules to tab content
        for ($i = 0; $i < $columns; $i++) {
            $currentCol = ($i + 1);

            if (in_array($currentCol, $skipColums)) {
                continue;
            }

            $tabContent = $builder->addTab('Column ' . $currentCol);
            $tabContent = $tabContent->conditional('columns', '==', trim($currentCol));

            for ($ii = 1; $ii <= $columns; $ii++) {
                $tabContent = $tabContent->or('columns', '==', trim($currentCol + $ii));
            }

            $flexContent = $tabContent->addFlexibleContent('modules_' . $currentCol, [
                'label' => 'Modules'
            ]);

            $this->flexModuleContents[] = $flexContent;
        }
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
     * @param FieldsBuilder $builder
     */
    protected function addSettings(FieldsBuilder &$builder)
    {
        $breakpoints = $this->getParam('breakpoints');

        $builder->addTab('Settings');
        $builder->addGroup('general', ['label' => 'General']);
        $builder->addText('label', ['label' => 'Label', 'wrapper' => ['width' => 33]]);
        $builder->addSelect('mode', ['label' => 'Layout Mode', 'wrapper' => ['width' => 33]])
                ->addChoices($this->layoutModes);

        $builder->addTrueFalse('reverse', ['label' => 'Reverse', 'wrapper' => ['width' => 33]]);

        // Alignment Vertical
        $builder->addGroup('alignment_vertical', ['label' => 'Vertical alignment']);

        foreach ($breakpoints as $bp) {
            $builder->addSelect(
                'alignment_vertical_' . strtolower($bp),
                [
                    'label' => strtoupper($bp),
                    'wrapper' => [
                        'width' => round(100 / count($breakpoints))
                    ]
                ]
            )->addChoices($this->verticalAlignments);
        }

        // Alignment Horizontal
        $builder->addGroup('alignment_horizontal', ['label' => 'Horizontal alignment']);

        foreach ($breakpoints as $bp) {
            $builder->addSelect(
                'alignment_horizontal_' . strtolower($bp),
                [
                    'label' => strtoupper($bp),
                    'wrapper' => [
                        'width' => round(100 / count($breakpoints))
                    ]
                ]
            )->addChoices($this->horizontalAlignments);
        }

        // Add column size settings
        $columns = $this->getParam('columns');
        $columnSizes = $this->getParam('columnSizes');
        $baseWidth = $this->getParam('baseWidth');
        $builder->addGroup('column_sizes', ['label' => 'Column sizes'])
                ->conditional('columns', '!=', '1');

        foreach ($breakpoints as $bp) {
            for ($i = 0; $i < $columns; $i++) {
                $currentCol = ($i + 1);

                if ($currentCol == 1) {
                    continue;
                }

                $sizeSet = [];
                $choices = [];

                if (array_key_exists($currentCol, $columnSizes)) {
                    $sizeSet = $columnSizes[$currentCol];
                } else {
                    $sizeSet = [array_fill(0, $currentCol, '1/1')];
                }

                foreach ($sizeSet as $sizes) {
                    $key = '';

                    foreach ($sizes as $size) {
                        $factor = 1;

                        if (is_int($size)) {
                            $factor = intval($size);
                        } elseif (is_string($size)) {
                            $parts = explode('/', $size);
                            $factor = intval($parts[0]) / intval($parts[1]);
                        }

                        $fract = round($baseWidth * $factor);
                        $key .= empty($key) ? $fract : ('-' . $fract);
                    }

                    $choices[] = [$key => implode(' - ', $sizes)];
                }

                $builder->addSelect(
                    'column_sizes_' . strtolower($bp) . '_' . $currentCol,
                    [
                        'label' => strtoupper($bp),
                        'wrapper' => [
                            'width' => round(100 / count($breakpoints))
                        ]
                    ]
                )
                ->addChoices($choices)
                ->conditional('columns', '==', trim($currentCol));
            }
        }
    }
}
