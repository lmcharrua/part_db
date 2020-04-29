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
use PartDB\Exceptions\DatabaseException;
use PartDB\Exceptions\ElementNotExistingException;
use PartDB\Exceptions\InvalidElementValueException;
use PartDB\Permissions\CPartAttributePermission;
use PartDB\Permissions\PermissionManager;

/**
 * @file Orderdetails.php
 * @brief class Orderdetails

 * @class Orderdetails
 * All elements of this class are stored in the database table "orderdetails".
 *
 * One Orderdetails-object includes these things:
 *  - 1 supplier (this is always required, you cannot have orderdetails without a supplier!)
 *  - 0..1 supplier-part-nr. (empty string means "no part-nr")
 *  - 0..* Pricedetails
 *
 * A Part can have more than one Orderdetails-object, which can have more than one Pricedetails-objects.
 *
 * @author kami89
 */
class Orderdetails extends Base\DBElement implements Interfaces\IAPIModel
{
    const TABLE_NAME = 'orderdetails';

    /********************************************************************************
     *
     *   Calculated Attributes
     *
     *   Calculated attributes will be NULL until they are requested for first time (to save CPU time)!
     *   After changing an element attribute, all calculated data will be NULLed again.
     *   So: the calculated data will be cached.
     *
     *********************************************************************************/

    /** @var Part the part of this orderdetails */
    private $part           = null;
    /** @var Supplier the supplier of this orderdetails */
    private $supplier       = null;
    /** @var array all pricedetails of this orderdetails, as a one-dimensional array of Pricedetails objects */
    private $pricedetails   = null;

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
        $this->part             = null;
        $this->supplier         = null;
        $this->pricedetails     = null;

        parent::resetAttributes($all);
    }

    /********************************************************************************
     *
     *   Basic Methods
     *
     ********************************************************************************
     * @throws Exception
     */

    public function setAttributes(array $new_values)
    {
        $this->current_user->tryDo(PermissionManager::PARTS_ORDERDETAILS, CPartAttributePermission::EDIT);
        parent::setAttributes($new_values);
    }

    /**
     * Delete this orderdetails incl. all their pricedetails
     *
     * @throws Exception if there was an error
     */
    public function delete()
    {
        $this->current_user->tryDo(PermissionManager::PARTS_ORDERDETAILS, CPartAttributePermission::DELETE);
        try {
            $transaction_id = $this->database->beginTransaction(); // start transaction

            // Delete all Pricedetails
            $all_pricedetails = array_reverse($this->getPricedetails()); // the last one must be deleted first!
            $this->resetAttributes(); // set $this->pricedetails to NULL
            foreach ($all_pricedetails as $pricedetails) {
                /** @var Pricedetails $pricedetails */
                $pricedetails->delete();
            }

            // Check if this Orderdetails is the Part's selected Orderdetails for ordering and delete this reference if neccessary
            $order_orderdetails = $this->getPart()->getOrderOrderdetails();
            if (\is_object($order_orderdetails) && ($order_orderdetails->getID() == $this->getID())) {
                $this->getPart()->setOrderOrderdetailsID(null);
            } else {
                $this->getPart()->setAttributes(array());
            } // save part attributes to update its "last_modified"

            // now we can delete this orderdetails
            parent::delete();

            $this->database->commit($transaction_id); // commit transaction
        } catch (Exception $e) {
            $this->database->rollback(); // rollback transaction

            // restore the settings from BEFORE the transaction
            $this->resetAttributes();

            throw new Exception(_("Die Einkaufsinformationen konnten nicht gelöscht werden!\n") . _('Grund: ') . $e->getMessage());
        }
    }

    /********************************************************************************
     *
     *   Getters
     *
     *********************************************************************************/

    /**
     * Get the part
     *
     * @return Part     the part of this orderdetails
     *
     * @throws DatabaseException if there was an error
     */
    public function getPart() : Part
    {
        if (! \is_object($this->part)) {
            $this->part = Part::getInstance(
                $this->database,
                $this->current_user,
                $this->log,
                (int) $this->db_data['part_id']
            );
        }

        return $this->part;
    }

    /**
     * Get the supplier
     *
     * @return Supplier     the supplier of this orderdetails
     *
     * @throws DatabaseException if there was an error
     */
    public function getSupplier() : Supplier
    {
        if (! \is_object($this->supplier)) {
            $this->supplier = Supplier::getInstance(
                $this->database,
                $this->current_user,
                $this->log,
                (int) $this->db_data['id_supplier']
            );
        }

        return $this->supplier;
    }

    /**
     * Get the supplier part-nr.
     *
     * @return string       the part-nr.
     */
    public function getSupplierPartNr() : string
    {
        return $this->db_data['supplierpartnr'];
    }

    /**
     * Get if this orderdetails is obsolete
     *
     * "Orderdetails is obsolete" means that the part with that supplier-part-nr
     * is no longer available from the supplier of that orderdetails.
     *
     * @return boolean      @li true if this part is obsolete at that supplier
     *                      @li false if this part isn't obsolete at that supplier
     */
    public function getObsolete() : bool
    {
        return (bool) $this->db_data['obsolete'];
    }

    /**
     * Get the link to the website of the article on the suppliers website.
     *
     * @param $no_automatic_url bool Set this to true, if you only want to get the local set product URL for this Orderdetail
     * and not a automatic generated one, based from the Supplier
     *
     * @return string           the link to the article
     * @throws Exception
     */
    public function getSupplierProductUrl(bool $no_automatic_url = false) : string
    {
        if ($no_automatic_url || $this->db_data['supplier_product_url'] != '') {
            return $this->db_data['supplier_product_url'];
        } else {
            return $this->getSupplier()->getAutoProductUrl($this->db_data['supplierpartnr']);
        } // maybe an automatic url is available...
    }

    /**
     * Get all pricedetails
     *
     * @return Pricedetails[]    all pricedetails as a one-dimensional array of Pricedetails objects,
     *                  sorted by minimum discount quantity
     *
     * @throws Exception if there was an error
     */
    public function getPricedetails() : array
    {
        if (!$this->current_user->canDo(PermissionManager::PARTS_PRICES, CPartAttributePermission::READ)) {
            return array();
        }

        if (! \is_array($this->pricedetails)) {
            $this->pricedetails = array();

            $query = 'SELECT * FROM pricedetails '.
                'WHERE orderdetails_id=? '.
                'ORDER BY min_discount_quantity ASC';

            $query_data = $this->database->query($query, array($this->getID()));

            foreach ($query_data as $row) {
                $this->pricedetails[] = Pricedetails::getInstance($this->database, $this->current_user, $this->log, (int) $row['id'], $row);
            }
        }

        return $this->pricedetails;
    }

    /**
     * Get the price for a specific quantity
     *
     * @param boolean $as_money_string      @li if true, this method returns a money string incl. currency
     *                                      @li if false, this method returns the price as float
     * @param integer       $quantity       this is the quantity to choose the correct pricedetails
     * @param integer|NULL  $multiplier     @li This is the multiplier which will be applied to every single price
     *                                      @li If you pass NULL, the number from $quantity will be used
     *
     * @return float|null|string    float: the price as a float number (if "$as_money_string == false")
     * * null: if there are no prices and "$as_money_string == false"
     * * string:   the price as a string incl. currency (if "$as_money_string == true")
     *
     * @throws Exception if there are no pricedetails for the choosed quantity
     *          (for example, there are only one pricedetails with the minimum discount quantity '10',
     *          but the choosed quantity is '5' --> the price for 5 parts is not defined!)
     * @throws Exception if there was an error
     *
     * @see floatToMoneyString()
     */
    public function getPrice(bool $as_money_string = false, int $quantity = 1, $multiplier = null)
    {
        if (($quantity == 0) && ($multiplier === null)) {
            if ($as_money_string) {
                return floatToMoneyString(0);
            } else {
                return 0;
            }
        }

        $all_pricedetails = $this->getPricedetails();

        if (count($all_pricedetails) == 0) {
            if ($as_money_string) {
                return floatToMoneyString(null);
            } else {
                return null;
            }
        }

        foreach ($all_pricedetails as $pricedetails) {
            // choose the correct pricedetails for the choosed quantity ($quantity)
            if ($quantity < $pricedetails->getMinDiscountQuantity()) {
                break;
            }

            $correct_pricedetails = $pricedetails;
        }

        if (! isset($correct_pricedetails) || (! \is_object($correct_pricedetails))) {
            throw new Exception(_('Es sind keine Preisinformationen für die angegebene Bestellmenge vorhanden!'));
        }

        if ($multiplier === null) {
            $multiplier = $quantity;
        }

        return $correct_pricedetails->getPrice($as_money_string, $multiplier);
    }

    /********************************************************************************
     *
     *   Setters
     *
     *********************************************************************************/

    /**
     * Set the supplier ID
     *
     * @param integer $new_supplier_id       the ID of the new supplier
     *
     * @throws Exception if the new supplier ID is not valid
     * @throws Exception if there was an error
     */
    public function setSupplierId(int $new_supplier_id)
    {
        $this->setAttributes(array('id_supplier' => $new_supplier_id));
    }

    /**
     * Set the supplier part-nr.
     *
     * @param string $new_supplierpartnr       the new supplier-part-nr
     *
     * @throws Exception if there was an error
     */
    public function setSupplierpartnr(string $new_supplierpartnr)
    {
        $this->setAttributes(array('supplierpartnr' => $new_supplierpartnr));
    }

    /**
     * Set if the part is obsolete at the supplier of that orderdetails
     *
     * @param boolean $new_obsolete       true means that this part is obsolete
     *
     * @throws Exception if there was an error
     */
    public function setObsolete(bool $new_obsolete)
    {
        $this->setAttributes(array('obsolete' => $new_obsolete));
    }

    /**
     * Sets the custom product supplier URL for this order detail.
     * Set this to "", if the function getSupplierProductURL should return the automatic generated URL.
     * @param $new_url string The new URL for the supplier URL.
     * @throws Exception if there was an error
     */
    public function setSupplierProductUrl(string $new_url)
    {
        $this->setAttributes(array('supplier_product_url' => $new_url));
    }

    /**
     * Returns the ID as an string, defined by the element class.
     * This should have a form like P000014, for a part with ID 14.
     * @return string The ID as a string;
     */
    public function getIDString(): string
    {
        return 'O' . sprintf('%06d', $this->getID());
    }

    /********************************************************************************
     *
     *   Static Methods
     *
     *********************************************************************************/


    /**
     * @copydoc DBElement::check_values_validity()
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public static function checkValuesValidity(Database $database, User $current_user, Log $log, array &$values, bool $is_new, &$element = null)
    {
        // first, we let all parent classes to check the values
        parent::checkValuesValidity($database, $current_user, $log, $values, $is_new, $element);

        // set the datetype of the boolean attributes
        $values['obsolete'] = (bool)$values['obsolete'];

        // check "part_id"
        try {
            $part = Part::getInstance($database, $current_user, $log, (int) $values['part_id']);
            $part->setAttributes(array()); // save part attributes to update its "last_modified"
        } catch (ElementNotExistingException $e) {
            throw new InvalidElementValueException(_('Das gewählte Bauteil existiert nicht!'));
        }

        // check "id_supplier"
        try {
            if ($values['id_supplier'] < 1) {
                throw new InvalidElementValueException('id_supplier < 1');
            }

            $supplier = Supplier::getInstance($database, $current_user, $log, (int) $values['id_supplier']);
        } catch (ElementNotExistingException $e) {
            throw new InvalidElementValueException(_('Der gewählte Lieferant existiert nicht!'));
        }
    }

    /**
     * @Create a new orderdetails record
     *
     * @param Database  &$database          reference to the database onject
     * @param User      &$current_user      reference to the current user which is logged in
     * @param Log       &$log               reference to the Log-object
     * @param integer   $part_id            the ID of the part with that the orderdetails is associated
     * @param integer   $supplier_id        the ID of the supplier (see Orderdetails::set_supplier_id())
     * @param string    $supplierpartnr     the supplier-part-nr (see Orderdetails::set_supplierpartnr())
     * @param boolean   $obsolete           the obsolete attribute of the new orderdetails (see Orderdetails::set_obsolete())
     *
     * @return Base\DBElement|Orderdetails
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
        int $part_id = null,
        int $supplier_id,
        string $supplierpartnr = '',
        bool $obsolete = false,
        string $supplier_product_url = ''
    ) : Orderdetails {
        $current_user->tryDo(PermissionManager::PARTS_ORDERDETAILS, CPartAttributePermission::CREATE);

        return parent::addByArray(
            $database,
            $current_user,
            $log,
            array(  'part_id'                   => $part_id,
                'id_supplier'               => $supplier_id,
                'supplierpartnr'            => $supplierpartnr,
                'obsolete'                  => $obsolete,
                'supplier_product_url' => $supplier_product_url)
        );
    }

    /**
     * Returns a Array representing the current object.
     * @param bool $verbose If true, all data about the current object will be printed, otherwise only important data is returned.
     * @return array A array representing the current object.
     * @throws Exception
     */
    public function getAPIArray(bool $verbose = false) : array
    {
        $json =  array( 'id' => $this->getID(),
            'supplierpartnr' => $this->getSupplierPartNr()
        );

        if ($verbose == true) {
            $ver = array('supplier' => $this->getSupplier()->getAPIArray(),
                'obsolete' => $this->getObsolete() == true,
                'supplier_product_url' => $this->getSupplierProductUrl(),
                'pricedetails' => convertAPIModelArray($this->getPricedetails(), true));
            return array_merge($json, $ver);
        }
        return $json;
    }
}
