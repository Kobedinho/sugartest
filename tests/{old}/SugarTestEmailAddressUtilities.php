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


class SugarTestEmailAddressUtilities
{
    private static $createdAddresses = array();

    private function __construct() {}

    public static function createEmailAddress($address = null)
    {
        if (null === $address)
        {
            $address = 'address-' . mt_rand() . '@example.com';
        }

        $email_address = new EmailAddress();
        $email_address->email_address = $address;
        $email_address->save();

        self::$createdAddresses[] = $email_address;
        return $email_address;
    }

    /**
     * Add specified email address to the person
     *
     * @param Person $person
     * @param string|EmailAddress $address
     * @param array $additional_values
     * @return boolean|EmailAddress
     * @throws InvalidArgumentException
     */
    public static function addAddressToPerson(Person $person, $address, array $additional_values = array())
    {
        if (is_string($address))
        {
            $address = self::createEmailAddress($address);
        }

        if (!$address instanceof EmailAddress)
        {
            throw new InvalidArgumentException(
                'Address must be a string or an instance of EmailAddress, '
                    . gettype($address) . ' given'
            );
        }

        if (!$person->load_relationship('email_addresses'))
        {
            return false;
        }

        // create relation between user and email address
        $person->email_addresses->add(array($address), $additional_values);
        $GLOBALS['db']->commit();
        return $address;
    }

    public static function removeAllCreatedAddresses()
    {
        $ids = self::getCreatedEmailAddressIds();
        if (count($ids) > 0)
        {
            $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $ids) . '\')');
        }
        self::$createdAddresses = array();
    }

    public static function getCreatedEmailAddressIds()
    {
        $ids = array();
        foreach (self::$createdAddresses as $address)
        {
            $ids[] = $address->id;
        }
        return $ids;
    }
}
