<?php

namespace AcfBreeze;

use AcfBreeze\Builder\Group;
use AcfBreeze\Builder\Layout;
use AcfBreeze\Models\Package\AbstractModel;
use AcfBreeze\Models\PackageModel;

class PackageBuilder
{
    /**
     * @var PackageModel
     */
    private $package = null;

    /**
     * @param PackageModel $package
     */
    public function __construct(PackageModel &$package)
    {
        $this->package = $package;
    }

    /**
     * @param PackageModel $package
     * @return array
     */
    protected function buildGroups()
    {
        $groups = [];

        foreach ($this->package->groups as $groupModel) {
            $group = new $groupModel->class($groupModel->name, $groupModel->params);
            $layouts = $groupModel->layoutSelector;

            if (is_array($layouts)) {
                $groupModel->layouts = $this->buildLayouts(
                    $layouts,
                    $group,
                    $groupModel
                );
            } elseif (is_string($layouts) && $layouts == '*') {
                $groupModel->layouts = $this->buildLayouts(
                    array_keys($this->package->layouts),
                    $group,
                    $groupModel
                );
            }

            $groupModel->params = array_replace_recursive(
                $groupModel->params,
                $group->getParams()
            );

            $groups[] = $group->build();
        }

        return $groups;
    }

    /**
     * @param array $layoutNames
     * @param Group $group
     * @param AbstractModel $parent
     * @return array
     */
    protected function buildLayouts($layoutNames, Group &$group, AbstractModel &$parent)
    {
        $layouts = [];

        foreach ($layoutNames as $name) {
            $layoutModel = $this->package->layouts[$name];
            $modules = $layoutModel->moduleSelector;
            $layout = new $layoutModel->class($layoutModel->name, $layoutModel->params);

            if (is_array($modules)) {
                $layoutModel->modules = $this->buildModules(
                    $modules,
                    $layout,
                    $layoutModel
                );
            } elseif (is_string($modules) && $modules == '*') {
                $layoutModel->modules = $this->buildModules(
                    array_keys($this->package->modules),
                    $layout,
                    $layoutModel
                );
            }

            $layoutModel->parent = $parent;
            $layoutModel->params = array_replace_recursive(
                $layoutModel->params,
                $layout->getParams()
            );

            $layouts[] = $layoutModel;

            $group->addLayout($layout);
        }

        return $layouts;
    }

    /**
     * @param array $moduleNames
     * @param Layout $group
     * @param AbstractModel $parent
     * @return array
     */
    protected function buildModules($moduleNames, Layout &$layout, AbstractModel $parent = null)
    {
        $modules = [];

        foreach ($moduleNames as $name) {
            $moduleModel = $this->package->modules[$name];
            $module = new $moduleModel->class($moduleModel->name, $moduleModel->params);

            $moduleModel->parent = $parent;
            $moduleModel->params = array_replace_recursive(
                $moduleModel->params,
                $module->getParams()
            );

            $modules[] = $moduleModel;

            $layout->addModule($module);
        }

        return $modules;
    }

    /**
     * @return array
     */
    public function build()
    {
        return $this->buildGroups();
    }
}
