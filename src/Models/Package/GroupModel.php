<?php

namespace AcfBreeze\Models\Package;

class GroupModel extends AbstractModel
{
    /**
     * @var array|string
     */
    public $layoutSelector = null;

    /**
     * @var array
     */
    public $layouts = [];

    /**
     * @var string
     */
    public $entry = '';

    /**
     * @var string
     */
    public $path = 'groups';

    /**
     * @var boolean
     */
    public $render = true;

    /**
     * @param string $name
     * @return self
     */
    private function getLayout($name)
    {
        foreach ($this->layouts as $layout) {
            if ($layout->name == $name) {
                return $layout;
            }
        }

        return null;
    }

    /**
     * @param integer $id
     * @return void
     */
    public function layouts($id = -1)
    {
        $layouts = [];
        $data = $this->data($id);

        if (is_array($data)) {
            foreach ($data as $data) {
                if (array_key_exists('acf_fc_layout', $data)) {
                    $layout = $this->getLayout($data['acf_fc_layout']);

                    if ($layout) {
                        $newLayout = clone $layout;
                        $newLayout->data = $data;

                        $layouts[] = $newLayout;
                    }
                }
            }
        }

        return $layouts;
    }
}
