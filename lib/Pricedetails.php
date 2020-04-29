<?php declare(strict_types=1);
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    part-db version 0.2+
    Copyright (C) 2009 K. Jacobs and others (see authors.php)
    http://code.google.com/p/part-db/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

namespace PartDB;

use Exception;
use PartDB\Exceptions\ElementNotExistingException;
use PartDB\Exceptions\InvalidElementValueException;
use PartDB\Permissions\CPartAttributePermission;
use PartDB\Permissions\PermissionManager;

/**
 * @file Pricedetails.php
 * @brief class Pricedetails

 * @class Pricedetails
 * All elements of this class are stored in the database table "pricedetails".
 *
 * One Pricedetails-object includes these things:
 *  - Price
 *  - Price related quantity (see Pricedetails::set_price_related_quantity())
 *  - Minimum discount quantity (see Pricedetails::set_min_discount_quantity())
 *
 * An Orderdetails object can have more than one Pricedetails objects.
 *
 * @author kami89
 */
class Pricedetails extends Base\DBElement implements Interfaces\IAPIModel
{
    const TABLE_NAME = 'pricedetails';

    /********************************************************************************
     *
     *   Calculated Attributes
     *
     *   Calculated attributes will be NULL until they are requested for first time (to save CPU time)!
     *   After changing an element attribute, all calculated data will be NULLed again.
     *   So: the calculated data will be cached.
     *
     *********************************************************************************/

    /** @var Orderdetails the orderdetails which includes this pricedetails */
    private $orderdetails   = null;

    /********************************************************************************
     *
     *   Constructor / Destructor / reset_attributes()
     *
     *********************************************************************************/

    /** This creates a new Element object, representing an entry from the Database.
     *
     * @param Database $database reference to the Database-object
     * @param User $current_user reference to the current user which is logged in
     * @param Log $log reference to the Log-object
     * @param integer $id ID of the element we want to get
     * @param array $db_data If you have already data from the database,
     * then use give it with this param, the part, wont make a database request.
     *
     * @throws \PartDB\Exceptions\TableNotExistingException If the table is not existing in the DataBase
     * @throws \PartDB\Exceptions\DatabaseException If an error happening during Database AccessDeniedException
     * @throws \PartDB\Exceptions\ElementNotExistingException If no such element exists in DB.
     */
    protected function __construct(Database $database, User $current_user, Log $log, int $id, $data = null)
    {
        parent::__construct($database, $current_user, $log, $id, $data);
    }

    /**
     * @copydoc DBElement::reset_attributes()
     */
    public function resetAttributes(bool $all = false)
    {
        $this->orderdetails = null;

        parent::resetAttributes($all);
    }

    /********************************************************************************
     *
     *   Basic Methods
     *
     ********************************************************************************
     * @throws Exception
     */

    public function setAttributes(array $new_values, $edit_message = null)
    {
        $this->current_user->tryDo(PermissionManager::PARTS_PRICES, CPartAttributePermission::EDIT);
        parent::setAttributes($new_values);
    }

    /**
     * Delete this pricedetails record
     *
     * @warning     The pricedetails with "min_discount_quantity == 1" cannot be deleted
     *              if there are already other pricedetails! If you want to delete all
     *              pricedetails, you have to delete all other pricedetails first!
     *
     * @throws Exception if it is not allowed to delete this pricedetails
     * @throws Exception if there was an error
     */
    public function delete()
    {
        $this->current_user->tryDo(PermissionManager::PARTS_PRICES, CPartAttributePermission::DELETE);
        // Check if it is allowed to delete this pricedetails
        if ($this->getMinDiscountQuantity() == 1) {
            $orderdetails = $this->getOrderdetails();
            $all_pricedetails = $orderdetails->getPricedetails();

            if (count($all_pricedetails) > 1) {
                throw new Exception(_('Die ausgewählte Preisinformation kann erst gelöscht werden '.
                    'wenn es sonst keine weiteren Preisinformationen gibt!'));
            }
        }

        // save orderdetails attributes to update its "last_modified" and "last_modified" of the part
        $this->getOrderdetails()->setAttributes(array());

        // now we can delete this orderdetails
        parent::delete();
    }

    /********************************************************************************
     *
     *   Getters
     *
     *********************************************************************************/

    /**
     * Get the orderdetails of this pricedetails
     *
     * @return Orderdetails     the orderdetails object
     *
     * @throws Exception if there was an error
     */
    public function getOrderdetails() : Orderdetails
    {
        if (! \is_object($this->orderdetails)) {
            $this->orderdetails = Orderdetails::getInstance(
                $this->database,
                $this->current_user,
                $this->log,
                (int)$this->db_data['orderdetails_id']
            );
        }

        return $this->orderdetails;
    }

    /**
     * Get the price
     *
     * @param boolean $as_money_string      @li if true, this method returns a money string incl. currency
     *                                      @li if false, this method returns the price as float
     * @param integer $multiplier           The returned price (float or string) will be multiplied
     *                                      with this multiplier.
     *
     * @note    You will get the price for $multiplier parts. If you want the price which is stored
     *          in the database, you have to pass the "price_related_quantity" count as $multiplier.
     *
     * @return float    the price as a float number (if "$as_money_string == false")
     * @return string   the price as a string incl. currency (if "$as_money_string == true")
     *
     * @see floatToMoneyString()
     */
    public function getPrice(bool $as_money_string = false, int $multiplier = 1)
    {
        $price = ($this->db_data['price'] * $multiplier) / $this->db_data['price_related_quantity'];

        if ($as_money_string) {
            return floatToMoneyString($price);
        } else {
            return $price;
        }
    }

    /**
     *  Get the price related quantity
     *
     * This is the quantity, for which the price is valid.
     *
     * @return integer       the price related quantity
     *
     * @see Pricedetails::setPriceRelatedQuantity()
     */
    public function getPriceRelatedQuantity() : int
    {
        return (int) $this->db_data['price_related_quantity'];
    }

    /**
     *  Get the minimum discount quantity
     *
     * "Minimum discount quantity" means the minimum order quantity for which the price
     * of this orderdetails is valid.
     *
     * @return integer       the minimum discount quantity
     *
     * @see Pricedetails::setMinDiscountQuantity()
     */
    public function getMinDiscountQuantity() : int
    {
        return (int) $this->db_data['min_discount_quantity'];
    }

    /********************************************************************************
     *
     *   Setters
     *
     *********************************************************************************/

    /**
     *  Set the price
     *
     * @param float $new_price       the new price as a float number
     *
     * @warning     @li This is the price for "price_related_quantity" parts!!
     *              @li Example: if "price_related_quantity" is '10',
     *                  you have to set here the price for 10 parts!
     *
     * @throws Exception if the new price is not valid
     * @throws Exception if there was an error
     */
    public function setPrice(float $new_price)
    {
        $this->setAttributes(array('price' => $new_price));
    }

    /**
     *  Set the price related quantity
     *
     * This is the quantity, for which the price is valid.
     *
     * @par Example:
     * If 100pcs costs 20$, you have to set the price to 20$ and the price related
     * quantity to 100. The single price (20$/100 = 0.2$) will be calculated automatically.
     *
     * @param integer $new_price_related_quantity the price related quantity
     * @throws Exception
     */
    public function setPriceRelatedQuantity(int $new_price_related_quantity)
    {
        $this->setAttributes(array('price_related_quantity' => $new_price_related_quantity));
    }

    /**
     *  Set the minimum discount quantity
     *
     * "Minimum discount quantity" means the minimum order quantity for which the price
     * of this orderdetails is valid. This way, you're able to use different prices
     * for different order quantities (quantity discount!).
     *
     * @par Example:
     *      - 1-9pcs costs 10$: set price to 10$/pcs and minimum discount quantity to 1
     *      - 10-99pcs costs 9$: set price to 9$/pcs and minimum discount quantity to 10
     *      - 100pcs or more costs 8$: set price/pcs to 8$ and minimum discount quantity to 100
     *
     * (Each of this examples would be an own Pricedetails-object.
     * So the orderdetails would have three Pricedetails for one supplier.)
     *
     * @param integer $new_min_discount_quantity the minimum discount quantity
     * @throws Exception
     */
    public function setMinDiscountQuantity(int $new_min_discount_quantity)
    {
        $this->setAttributes(array('min_discount_quantity' => $new_min_discount_quantity));
    }

    /********************************************************************************
     *
     *   Static Methods
     *
     *********************************************************************************/

    /**
     * @copydoc PartDB\DBElement::check_values_validity()
     * @param Pricedetails $element
     * @throws Exception
     */
    public static function checkValuesValidity(Database $database, User $current_user, Log $log, array &$values, bool $is_new, &$element = null)
    {
        // first, we let all parent classes to check the values
        parent::checkValuesValidity($database, $current_user, $log, $values, $is_new, $element);

        // set the type of the boolean attributes
        $values['manual_input'] = (bool)$values['manual_input'];

        // check "orderdetails_id"
        try {
            $orderdetails = Orderdetails::getInstance($database, $current_user, $log, (int) $values['orderdetails_id']);

            // save orderdetails attributes to update its "last_modified" and "last_modified" of the part
            $orderdetails->setAttributes(array());
        } catch (ElementNotExistingException $e) {
            throw new InvalidElementValueException(_('Die gewählten Einkaufsinformationen existieren nicht!'));
        }

        // check "price"
        if ((! is_numeric($values['price'])) || ($values['price'] < 0)) {
            throw new InvalidElementValueException(_('Der neue Preis ist ungültig!'));
        }

        // check "price_related_quantity"
        if (((! \is_int($values['price_related_quantity'])) && (! ctype_digit($values['price_related_quantity'])))
            || ($values['price_related_quantity'] < 1)) {
            throw new InvalidElementValueException(_('Die Preisbezogene Menge ist ungültig!'));
        }

        // check "min_discount_quantity"
        if (((! \is_int($values['min_discount_quantity'])) && (! ctype_digit($values['min_discount_quantity'])))
            || ($values['min_discount_quantity'] < 1)) {
            throw new InvalidElementValueException(_('Die Mengenrabatt-Menge ist ungültig!'));
        }

        // search for pricedetails with the same "min_discount_quantity"
        $same_min_discount_quantity_count = 0;
        $all_pricedetails = $orderdetails->getPricedetails();
        foreach ($all_pricedetails as $pricedetails) {
            if ($pricedetails->getMinDiscountQuantity() == $values['min_discount_quantity']) {
                $same_min_discount_quantity_count++;
            }
        }

        if ($is_new) {
            // first pricedetails, but "min_discount_quantity" != 1 ?
            if ((count($all_pricedetails) == 0) && ($values['min_discount_quantity'] != 1)) {
                throw new InvalidElementValueException(
                    _('Die Mengenrabatt-Menge muss bei der ersten Preisangabe "1" sein!')
                );
            }

            // is there already a pricedetails with the same "min_discount_quantity" ?
            if ($same_min_discount_quantity_count > 0) {
                throw new InvalidElementValueException(
                    _('Es existiert bereits eine Preisangabe für die selbe Mengenrabatt-Menge!')
                );
            }
        } elseif ($values['min_discount_quantity'] != $element->getMinDiscountQuantity()) {
            // does the user try to change the "min_discount_quantity", but it is "1" ?
            if ($element->getMinDiscountQuantity() == 1) {
                throw new InvalidElementValueException(
                    _('Die Mengenrabatt-Menge beim Preis für ein Bauteil kann nicht verändert werden!')
                );
            }

            // change the "min_discount_quantity" to a already existing value?
            if ($same_min_discount_quantity_count > 0) {
                throw new InvalidElementValueException(
                    _('Es existiert bereits eine Preisangabe mit der selben Mengenrabatt-Menge!')
                );
            }
        }
    }

    /**
     *  Create a new orderdetails record
     *
     * @param Database  &$database                  reference to the database onject
     * @param User      &$current_user              reference to the current user which is logged in
     * @param Log       &$log                       reference to the Log-object
     * @param integer   $orderdetails_id            the ID of the orderdetails with that the pricedetails is associated
     * @param float     $price                      the price of the part (see Pricedetails::set_price())
     * @param integer   $price_related_quantity     the price related quantity (see Pricedetails::set_price_related_quantity())
     * @param integer   $min_discount_quantity      the minimum discount quantity (see Pricedetails::set_min_discount_quantity())
     *
     * @note   The database column "last_update" will be filled automatically
     *         in Pricedetails::check_values_validity().
     *
     * @warning     The attribute "min_discount_quantity" must be "1" if there are no other
     *              pricedetails in the selected orderdetails yet!
     *
     * @return Base\DBElement|Pricedetails
     *
     * @throws Exception    if (this combination of) values is not valid
     * @throws Exception    if there was an error
     *
     * @see DBElement::add()
     */
    public static function add(
        Database $database,
        User $current_user,
        Log $log,
        int $orderdetails_id,
        float $price,
        int $price_related_quantity = 1,
        int $min_discount_quantity = 1
    ) : Pricedetails {
        $current_user->tryDo(PermissionManager::PARTS_PRICES, CPartAttributePermission::CREATE);

        return parent::addByArray(
            $database,
            $current_user,
            $log,
            array(  'orderdetails_id'           => $orderdetails_id,
                'manual_input'              => true,
                'price'                     => $price,
                'price_related_quantity'    => $price_related_quantity,
                'min_discount_quantity'     => $min_discount_quantity)
        );
    }

    /**
     * Returns a Array representing the current object.
     * @param bool $verbose If true, all data about the current object will be printed, otherwise only important data is returned.
     * @return array A array representing the current object.
     */
    public function getAPIArray(bool $verbose = false) : array
    {
        $json =  array( 'id' => $this->getID(),
            'quantity' => $this->getPriceRelatedQuantity(),
            'price' => $this->getPrice(),
            'minDiscountQuantity' => $this->getMinDiscountQuantity()
        );
        return $json;
    }

    /**
     * Returns the ID as an string, defined by the element class.
     * This should have a form like P000014, for a part with ID 14.
     * @return string The ID as a string;
     */
    public function getIDString(): string
    {
        return 'PD' . sprintf('%06d', $this->getID());
    }
}
