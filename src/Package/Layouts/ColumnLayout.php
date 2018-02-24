<?php

namespace AcfBreeze\Package\Layouts;

use AcfBreeze\Builder\Field;
use AcfBreeze\Builder\Layout;
use AcfBreeze\FieldsBuilder;
use StoutLogic\AcfBuilder\FieldsBuilder as StoutLogicFieldsBuilder;

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

        $this->addGroups($builder);
    }

    /**
     * @param FieldsBuilder $builder
     */
    protected function addGroups(FieldsBuilder &$builder)
    {
        // General settings
        $this->addGeneralSettings($builder);

        // Add content options
        $builder->addTab('settings');

        $this->addColumnSelection($builder);
        $this->addLayoutSettings($builder);
        $this->addLayoutAlignments($builder);
        $this->addColumnSizes($builder);
        $this->addColumnTabs($builder);
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
     * @return void
     */
    protected function addColumnSelection(StoutLogicFieldsBuilder &$field)
    {
        $skipColums = $this->getParam('skipColumns');
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

        $field->addSelect('columns', ['label' => ''])
                ->addChoices($columnChoices);
    }

    /**
     * @return void
     */
    protected function addColumnTabs(StoutLogicFieldsBuilder &$field)
    {
        $skipColums = $this->getParam('skipColumns');
        $columns = $this->getParam('columns');

        for ($i = 0; $i < $columns; $i++) {
            $currentCol = ($i + 1);

            if (in_array($currentCol, $skipColums)) {
                continue;
            }

            $tabContent = $field->addTab('Column ' . $currentCol);
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
     * @param StoutLogicFieldsBuilder $field
     * @return void
     */
    protected function addGeneralSettings(StoutLogicFieldsBuilder &$field)
    {
        $field->addText('layout_title', ['label' => 'Label', 'wrapper' => ['width' => 20]]);
    }

    /**
     * @param StoutLogicFieldsBuilder $field
     * @return void
     */
    protected function addLayoutAlignments(StoutLogicFieldsBuilder &$field)
    {
        $breakpoints = $this->getParam('breakpoints');

        // Vertical alignment
        $field->addMessage('Vertical alignment', 'Define the vertical alignment of the content for each breakpoint');

        foreach ($breakpoints as $bp) {
            $field->addSelect(
                'alignment_vertical_' . strtolower($bp),
                [
                    'label' => strtoupper($bp),
                    'wrapper' => [
                        'width' => round(100 / count($breakpoints))
                    ]
                ]
            )->addChoices($this->verticalAlignments);
        }

        // Horizontal alignment
        $field->addMessage('Horizontal alignment', 'Define the horizontal alignment of the content for each breakpoint');

        foreach ($breakpoints as $bp) {
            $field->addSelect(
                'alignment_horizontal_' . strtolower($bp),
                [
                    'label' => strtoupper($bp),
                    'wrapper' => [
                        'width' => round((100) / count($breakpoints))
                    ]
                ]
            )->addChoices($this->horizontalAlignments);
        }
    }

    /**
     * @param StoutLogicFieldsBuilder $field
     * @return void
     */
    protected function addColumnSizes(StoutLogicFieldsBuilder &$field)
    {
        $breakpoints = $this->getParam('breakpoints');
        $columns = $this->getParam('columns');
        $columnSizes = $this->getParam('columnSizes');
        $baseWidth = $this->getParam('baseWidth');
        $field->addMessage('Column sizes', 'The column sizes for each column on each breakpoint')
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

                $select = $field->addSelect(
                    'column_sizes_' . strtolower($bp) . '_' . $currentCol,
                    [
                        'label' => strtoupper($bp),
                        'wrapper' => [
                            'width' => round(100 / count($breakpoints))
                        ]
                    ]
                );

                $select->addChoices($choices);
                $select->conditional('columns', '==', trim($currentCol));
            }
        }
    }

    /**
     * @param StoutLogicFieldsBuilder $field
     * @return void
     */
    protected function addLayoutSettings(StoutLogicFieldsBuilder &$field)
    {
        $field->addSelect('mode', ['label' => 'Layout Mode', 'wrapper' => ['width' => 50]])
                     ->addChoices($this->layoutModes);
        $field->addTrueFalse('reverse', ['label' => 'Reverse', 'wrapper' => ['width' => 50]]);
    }
}
