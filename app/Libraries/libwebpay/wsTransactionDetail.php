<?php

/*
 * Copyright (C) 2016 Cristian Tala Sánchez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ctala\tbkwslib\classes;

class wsTransactionDetail {

    /**
     * @var float $sharesAmount
     * @access public
     */
    public $sharesAmount = null;

    /**
     * @var int $sharesNumber
     * @access public
     */
    public $sharesNumber = null;

    /**
     * @var float $amount
     * @access public
     */
    public $amount = null;

    /**
     * @var string $commerceCode
     * @access public
     */
    public $commerceCode = null;

    /**
     * @var string $buyOrder
     * @access public
     */
    public $buyOrder = null;

    /**
     * @param float $sharesAmount
     * @param int $sharesNumber
     * @param float $amount
     * @param string $commerceCode
     * @param string $buyOrder
     * @access public
     */
    public function __construct($sharesAmount, $sharesNumber, $amount, $commerceCode, $buyOrder) {
        $this->sharesAmount = $sharesAmount;
        $this->sharesNumber = $sharesNumber;
        $this->amount = $amount;
        $this->commerceCode = $commerceCode;
        $this->buyOrder = $buyOrder;
    }

}
