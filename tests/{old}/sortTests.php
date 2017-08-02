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
/**
 * This function sorts the tests alphabetically to make the
 * test runs more consistent.
 **/
function sortTests() {
    // Load old xml file
    $suitesXML = simplexml_load_file('phpuc.xml');
    if (!file_exists('phpucOLD.xml')) {
        copy('phpuc.xml','phpucOLD.xml');
    } else {
        return;
    }
    $suitesArray = toArray($suitesXML);
    unset($suitesXML->testsuites->testsuite);
    $suitesXML->testsuites->testsuite = "";
    // add tests manually
    foreach($suitesArray['testsuites']['testsuite']['directory'] as $directoryIdx => $directoryName) {
        $files =scanFileNameRecursively($directoryName);
        sort($files, SORT_STRING);
        $suitesXML->testsuites->testsuite->addChild($directoryName);
        foreach($files as $fileName){
            $suitesXML->testsuites->testsuite->$directoryName->addChild('file',$fileName);
        }
    }
    // write out new file
    file_put_contents('phpuc.xml',$suitesXML->asXML());
}
/**
 * Returns a list of files in a directory recursively with the word test in the filename
 **/
function scanFileNameRecursively($path = '', &$name = array() )
{
    $path = $path == ''? dirname(__FILE__) : $path;
    $lists = @scandir($path);

    if(!empty($lists))
    {
        foreach($lists as $f)
        {

            if(is_dir($path.DIRECTORY_SEPARATOR.$f) && $f != ".." && $f != ".")
            {
                scanFileNameRecursively($path.DIRECTORY_SEPARATOR.$f, $name);
            }
            elseif ($f != ".." && $f != "." && stripos($f, 'test') !== false && stripos($f, '.php') !== false)
            {
                $name[] = $path.DIRECTORY_SEPARATOR.$f;
            }
        }
    }
    return $name;
}
/**
 * Converts xml to a basic array
 **/
function toArray(SimpleXMLElement $xml) {
    $array = (array)$xml;
    foreach ( array_slice($array, 0) as $key => $value ) {
        if ( $value instanceof SimpleXMLElement ) {
            $array[$key] = empty($value) ? NULL : toArray($value);
        }
    }
    return $array;
}