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

class transactionResultOutput {

    /**
     * @var string $accountingDate
     * @access public
     */
    public $accountingDate = null;

    /**
     * @var string $buyOrder
     * @access public
     */
    public $buyOrder = null;

    /**
     * @var cardDetail $cardDetail
     * @access public
     */
    public $cardDetail = null;

    /**
     * @var wsTransactionDetailOutput[] $detailOutput
     * @access public
     */
    public $detailOutput = null;

    /**
     * @var string $sessionId
     * @access public
     */
    public $sessionId = null;

    /**
     * @var dateTime $transactionDate
     * @access public
     */
    public $transactionDate = null;

    /**
     * @var string $urlRedirection
     * @access public
     */
    public $urlRedirection = null;

    /**
     * @var string $VCI
     * @access public
     */
    public $VCI = null;

    /**
     * @param string $accountingDate
     * @param string $buyOrder
     * @param cardDetail $cardDetail
     * @param string $sessionId
     * @param dateTime $transactionDate
     * @param string $urlRedirection
     * @param string $VCI
     * @access public
     */
    public function __construct($accountingDate, $buyOrder, $cardDetail, $sessionId, $transactionDate, $urlRedirection, $VCI) {
        $this->accountingDate = $accountingDate;
        $this->buyOrder = $buyOrder;
        $this->cardDetail = $cardDetail;
        $this->sessionId = $sessionId;
        $this->transactionDate = $transactionDate;
        $this->urlRedirection = $urlRedirection;
        $this->VCI = $VCI;
    }

}
