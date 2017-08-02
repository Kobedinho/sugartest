<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit;

use Composer\Autoload\ClassMapGenerator;

/**
 *
 * Helper class to bootstrap the auto loader loading all available classes
 * into the classmap. Once all files are fully auto loaded directly from
 * within SugarAutoLoader this helper class will not be necessary anymore.
 *
 */
class AutoLoaderHelper
{
    /**
     * Absolute path to sugarcrm
     * @var string
     */
    protected $baseDir;

    /**
     * List of directories relative to sugar instance to scan for classes
     * @var array
     */
    protected $classMapDirs = array();

    /**
     * Set class map directories to scan
     * @param array $classMapDirs
     */
    public function setClassMapDirs(array $classMapDirs)
    {
        $this->classMapDirs = $classMapDirs;
    }

    /**
     * Set sugarcrm base directory
     * @param string $baseDir Absolute path to sugarcrm
     */
    public function setBaseDir($baseDir)
    {
        $this->baseDir = rtrim($baseDir, '/');
    }

    /**
     * Merge generated class map with given class map
     * @param array $classMap
     * @return array
     */
    public function mergeClassMap(array $classMap)
    {
        return array_merge($this->generateClassMap(), $classMap);
    }

    /**
     * Generate class map based on given class map directories
     * @return array
     */
    public function generateClassMap()
    {
        $classMap = array();
        foreach ($this->classMapDirs as $dir) {
            $dir = $this->baseDir . '/' . $dir;
            foreach (ClassMapGenerator::createMap($dir) as $class => $path) {
                $classMap[$class] = $this->getRelativePath($path);
            }
        }
        return $classMap;
    }

    /**
     * Return relative class file path
     * @param string $path
     * @return string
     */
    public function getRelativePath($path)
    {
        return str_replace($this->baseDir . '/', '', $path);
    }
}
