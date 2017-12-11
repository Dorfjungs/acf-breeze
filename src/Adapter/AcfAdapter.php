<?php

namespace AcfBreeze\Adapter;

class AcfAdapter
{
    /**
     * @param array $name
     * @param integer $id
     * @return array
     */
    public function getField($name, $id = -1)
    {
        return get_field($name, $id > -1 ? $id : false);
    }

    /**
     * @param array $config
     * @param array $data
     * @return void
     */
    public function getClassesFromData($config, $data)
    {
        $classes = [];

        foreach ($config as $configValue) {
            if (is_array($configValue)) {
                if (count($configValue) >= 2) {
                    $prefix = $configValue[0];
                    $suffixes = $configValue[1];
                    $appendToValue = array_key_exists(2, $configValue)
                                        ? (bool)$configValue[2]
                                        : false;

                    if ($prefix && $suffixes && is_array($suffixes)) {
                        foreach ($suffixes as $suffix) {
                            $key = $prefix . $suffix;

                            if (array_key_exists($key, $data)) {
                                $value = $data[$key];

                                $this->parseClass(
                                    $key,
                                    $value,
                                    $classes,
                                    true === $appendToValue
                                        ? $suffix
                                        : ''
                                );
                            }
                        }
                    }
                }
            } elseif (array_key_exists($configValue, $data)) {
                $name = $configValue;

                $this->parseClass($name, $data[$name], $classes);
            }
        }

        return empty($classes) ? '' : implode(' ', $classes);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array $list
     * @param string $append
     * @return string
     */
    protected function parseClass($name, $value, &$list, $append = '')
    {
        if ( ! empty($value)) {
            $class = '';

            if (is_string($value)) {
                $class = trim($value);
            } elseif (is_int($value)) {
                $class = $name  . '-' . trim($value);
            } elseif (is_bool($value) && true === $value) {
                $class = $name;
            }

            if ( ! empty($class)) {
                if ( ! empty($append)) {
                    $class .= '-' . $append;
                }

                $list[] = $class;
            }
        }
    }
}
