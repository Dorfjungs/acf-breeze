<?php

namespace AcfBreeze;

use AcfBreeze\Models\Package\GroupModel;
use AcfBreeze\Models\Package\LayoutModel;
use AcfBreeze\Models\Package\ModuleModel;
use AcfBreeze\Models\PackageModel;
use Timber;

class AcfBreeze
{
    /**
     * @var string
     */
    const DEFAULT_PACKAGE = 'acfbreeze';

    /**
     * @var array
     */
    private static $configRegistry = [];

    /**
     * @var array
     */
    private $packages = [];

    /**
     * @param array $names
     */
    public function __construct($names = [])
    {
        if ( ! class_exists('\acf')) {
            throw new Exceptions\AcfNotFoundException(
                'Can\'t detect ACF. Make sure it\'s installed'
            );
        }

        static::registerDefaultPackage();

        foreach ($names as $name) {
            if (array_key_exists($name, $this->packages)) {
                throw new Exceptions\PackageAlreadyEnabledException(
                    sprintf('Package "%s" already enabled', $name)
                );
            }

            if (array_key_exists($name, static::$configRegistry)) {
                $this->packages[$name] = static::parsePackage($name, static::$configRegistry[$name]);
            } else {
                throw new Exceptions\PackageNotFoundException(
                    sprintf('The pacakge "%s" was not registered', $name)
                );
            }
        }

        add_action('acf/init', function () {
            $this->init();
        });
    }

    public function init()
    {
        foreach ($this->packages as $name => $package) {
            // Build groups from config and add them to acf
            $builder = new PackageBuilder($package);
            $groups = $builder->build();

            foreach ($groups as $group) {
                acf_add_local_field_group($group);
            }

            // Add template locations for twig with timber
            if (class_exists('\Timber')) {
                foreach ($package->paths as $path) {
                    if ( ! is_string($path) || ! is_dir($path)) {
                        throw new Exceptions\InvalidPathException(
                            sprintf(
                                'The path "%s" is not valid or doesn\'t exist',
                                is_string($path) ? $path : gettype($path)
                            )
                        );
                    }

                    Timber::$locations[] = $path;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param string $name
     * @param array $config
     * @return void
     */
    public static function register($name, $config)
    {
        if (array_key_exists($name, static::$configRegistry)) {
            throw new Exceptions\PackageRegisteredException(
                sprintf('A package with the name "%s" already registered', $name)
            );
        }

        static::registerDefaultPackage();

        static::$configRegistry[$name] = static::parseConfig($name, $config);
    }

    /**
     * @return void
     */
    private static function registerDefaultPackage()
    {
        if ( ! array_key_exists(self::DEFAULT_PACKAGE, static::$configRegistry)) {
            static::$configRegistry[self::DEFAULT_PACKAGE] = static::parseConfig(
                self::DEFAULT_PACKAGE,
                static::getDefaultConfig()
            );
        }
    }

    /**
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            'paths' => [
                realpath(__DIR__ . '/../templates')
            ],
            'groups' => [
                'content_group' => [
                    'class' => Package\Groups\ContentGroup::class,
                    'entry' => 'flex_content',
                    'layouts' => '*'
                ]
            ],
            'layouts' => [
                'column_layout' => [
                    'class' => Package\Layouts\ColumnLayout::class,
                    'modules' => '*'
                ]
            ],
            'modules' => [
                'spacer_module' => Package\Modules\SpacerModule::class
            ]
        ];
    }

    /**
     * @param string $name
     * @param array $config
     * @return void
     */
    private static function parseConfig($name, $config)
    {
        static::parsePackageName($name, $config, ['groups', 'layouts', 'modules']);

        if (array_key_exists('extends', $config)) {
            $extendName = $config['extends'];

            if (array_key_exists($extendName, static::$configRegistry)) {
                $parent = static::$configRegistry[$extendName];

                unset($config['extends']);
                $config['parent'] = $parent;

                return array_replace_recursive($parent, $config);
            }

            throw new Exceptions\PackageNotFoundException(
                    sprintf(
                        'Cant\'t inherit from "%s" in "%s". Package "%s" not found',
                        $extendName,
                        $name,
                        $extendName
                    )
                );
        }

        return $config;
    }

    /**
     * @param string $name
     * @param array $config
     * @param array $keys
     * @return void
     */
    private static function parsePackageName($name, &$config, $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $config) && is_array($config[$key])) {
                foreach ($config[$key] as $keyName => &$keyConfig) {
                    if (is_string($keyConfig)) {
                        $config[$key][$keyName] = [
                            'class' => $keyConfig
                        ];
                    }

                    if (is_array($keyConfig) && ! array_key_exists('package', $keyConfig)) {
                        $keyConfig['package'] = $name;
                    }
                }
            }
        }
    }

    /**
     * @param string $pkgName
     * @param array $config
     * @return AcfBreeze\Models\PackageModel
     */
    private static function parsePackage($pkgName, $config)
    {
        $package = new PackageModel($pkgName);

        if (array_key_exists('parent', $config)) {
            $parent = $config['parent'];

            if (array_key_exists('paths', $parent)) {
                foreach ($parent['paths'] as $path) {
                    $package->paths[] = $path;
                }
            }
        }

        if (array_key_exists('paths', $config)) {
            foreach ($config['paths'] as $path) {
                $package->paths[] = $path;
            }
        }

        if (array_key_exists('groups', $config) && is_array($config['groups'])) {
            foreach ($config['groups'] as $name => $option) {
                $model = static::parseModel(new GroupModel($name), $option);

                if ( ! $model) {
                    if (array_key_exists($name, $package->groups)) {
                        unset($package->groups[$name]);
                    }
                } else {
                    if (array_key_exists('layouts', $option)) {
                        $model->layoutSelector = $option['layouts'];
                    }

                    if (array_key_exists('render', $option)) {
                        $model->render = $option['render'];
                    }

                    if (array_key_exists('entry', $option)) {
                        $model->entry = $option['entry'];
                    }

                    $package->groups[$name] = $model;
                }
            }
        }

        if (array_key_exists('layouts', $config) && is_array($config['layouts'])) {
            foreach ($config['layouts'] as $name => $option) {
                $model = static::parseModel(new LayoutModel($name), $option);

                if ( ! $model) {
                    if (array_key_exists($name, $package->layouts)) {
                        unset($package->layouts[$name]);
                    }
                } else {
                    if (array_key_exists('modules', $option)) {
                        $model->moduleSelector = $option['modules'];
                    }

                    $package->layouts[$name] = $model;
                }
            }
        }

        if (array_key_exists('modules', $config) && is_array($config['modules'])) {
            foreach ($config['modules'] as $name => $option) {
                $model = static::parseModel(new ModuleModel($name), $option);

                if ( ! $model) {
                    if (array_key_exists($name, $package->modules)) {
                        unset($package->modules[$name]);
                    }
                } else {
                    $package->modules[$name] = $model;
                }
            }
        }

        return $package;
    }

    /**
     * @param string $ctor
     * @param string|array $option
     * @return AcfBreeze\Models\Package\AbstractModel
     */
    private static function parseModel($model, $option)
    {
        if (is_null($option) || false === $option) {
            return null;
        }

        if (is_array($option)) {
            if (array_key_exists('package', $option)) {
                $model->package = $option['package'];
            }

            if (array_key_exists('class', $option)) {
                $class = $option['class'];

                if (class_exists($class)) {
                    $model->class = $class;
                } else {
                    throw new Exceptions\InvalidOptionException(
                        sprintf('Class for "%s" not found', $model->name)
                    );
                }
            }

            if (array_key_exists('params', $option)) {
                $params = $option['params'];

                if (is_array($params)) {
                    $model->params = $option['params'];
                } else {
                    throw new Exceptions\InvalidOptionException(
                        sprintf('Invalid params for "%s" provided', $model->name)
                    );
                }
            }
        } else {
            if (class_exists($option)) {
                $model->class = $option;
            } else {
                throw new Exceptions\InvalidOptionException(
                    sprintf(
                        'Invalid option "%s" for "%s" provided. Maybe the class was not found',
                        $option,
                        $model->name
                    )
                );
            }
        }

        return $model;
    }
}
