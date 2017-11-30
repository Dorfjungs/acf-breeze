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
    public $entry = 'flex_content';

    /**
     * @var string
     */
    public $path = 'groups';

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
     * @return void
     */
    public function layouts()
    {
        $layouts = [];

        foreach ($this->data() as $data) {
            if (array_key_exists('acf_fc_layout', $data)) {
                $layout = $this->getLayout($data['acf_fc_layout']);

                if ($layout) {
                    $newLayout = clone $layout;
                    $newLayout->data = $data;

                    $layouts[] = $newLayout;
                }
            }
        }

        return $layouts;
    }
}
