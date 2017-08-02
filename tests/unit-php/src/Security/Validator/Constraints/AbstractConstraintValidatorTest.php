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

namespace Sugarcrm\SugarcrmTests\Security\Validator\Constraints;

use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest as AbstractBase;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validation;

/**
 *
 * Abstract contraint validator test.
 *
 */
abstract class AbstractConstraintValidatorTest extends AbstractBase
{
    /**
     * {@inheritdoc}
     *
     * Workaround:
     *
     * Overwrite base method to be able to avoid calling \Locale which may
     * not be present on every stack as `intl` is not a required module.
     */
    protected function setUp()
    {
        if (Validation::API_VERSION_2_5 !== $this->getApiVersion()) {
            $this->iniSet('error_reporting', -1 & ~E_USER_DEPRECATED);
        }

        $this->group = 'MyGroup';
        $this->metadata = null;
        $this->object = null;
        $this->value = 'InvalidValue';
        $this->root = 'root';
        $this->propertyPath = 'property.path';

        // Initialize the context with some constraint so that we can
        // successfully build a violation.
        $this->constraint = new NotNull();

        $this->context = $this->createContext();
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        // @sugarcrm - commented out, see note in method docblock
        //\Locale::setDefault('en');

        $this->setDefaultTimezone('UTC');
    }
}
