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


class PHPUnit_Framework_SugarBeanRelated_TestCase extends PHPUnit_Framework_TestCase
{
    public function getBeanMock($className = null)
    {
        $builder = $this->getMockBuilder('SugarBean')->disableOriginalConstructor();

        if($className)
        {
            $builder->setMockClassName($className);
        }

        return $builder->getMock();
    }
}