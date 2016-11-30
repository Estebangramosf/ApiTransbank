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
use ctala\tbkwslib\classes\wsTransactionDetail;


class wsTransactionDetailOutput extends wsTransactionDetail {

    /**
     * @var string $authorizationCode
     * @access public
     */
    public $authorizationCode = null;

    /**
     * @var string $paymentTypeCode
     * @access public
     */
    public $paymentTypeCode = null;

    /**
     * @var int $responseCode
     * @access public
     */
    public $responseCode = null;

    /**
     * @param float $sharesAmount
     * @param int $sharesNumber
     * @param float $amount
     * @param string $commerceCode
     * @param string $buyOrder
     * @param string $authorizationCode
     * @param string $paymentTypeCode
     * @param int $responseCode
     * @access public
     */
    public function __construct($sharesAmount, $sharesNumber, $amount, $commerceCode, $buyOrder, $authorizationCode, $paymentTypeCode, $responseCode) {
        parent::__construct($sharesAmount, $sharesNumber, $amount, $commerceCode, $buyOrder);
        $this->authorizationCode = $authorizationCode;
        $this->paymentTypeCode = $paymentTypeCode;
        $this->responseCode = $responseCode;
    }

}
