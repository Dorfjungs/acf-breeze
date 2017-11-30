<?php

namespace AcfBreeze\Models\Package;

class LayoutModel extends AbstractModel
{
    /**
     * @var array|string
     */
    public $moduleSelector = null;

    /**
     * @var array
     */
    public $modules = [];

    /**
     * @var string
     */
    public $path = 'layouts';

    /**
     * @param string $name
     * @return self
     */
    private function getModule($name)
    {
        foreach ($this->modules as $module) {
            if ($module->name == $name) {
                return $module;
            }
        }

        return null;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function modules(...$keys)
    {
        $modules = [];
        $data = $this->data();
        $keys = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($keys)
        );

        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $dataValue = $data[$key];

                if (is_array($dataValue)) {
                    foreach ($dataValue as $dataKeyValue) {
                        if (array_key_exists('acf_fc_layout', $dataKeyValue)) {
                            $module = $this->getModule($dataKeyValue['acf_fc_layout']);
                            if ($module) {
                                $newModule = clone $module;
                                $newModule->data = $dataKeyValue;

                                $modules[] = $newModule;
                            }
                        }
                    }
                }
            }
        }

        return $modules;
    }
}
