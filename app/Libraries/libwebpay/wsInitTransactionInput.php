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

class wsInitTransactionInput
{

    /**
     * @var wsTransactionType $wSTransactionType
     * @access public
     */
    public $wSTransactionType = null;

    /**
     * @var string $commerceId
     * @access public
     */
    public $commerceId = null;

    /**
     * @var string $buyOrder
     * @access public
     */
    public $buyOrder = null;

    /**
     * @var string $sessionId
     * @access public
     */
    public $sessionId = null;

    /**
     * @var anyURI $returnURL
     * @access public
     */
    public $returnURL = null;

    /**
     * @var anyURI $finalURL
     * @access public
     */
    public $finalURL = null;

    /**
     * @var wsTransactionDetail[] $transactionDetails
     * @access public
     */
    public $transactionDetails = null;

    /**
     * @var wpmDetailInput $wPMDetail
     * @access public
     */
    public $wPMDetail = null;

    /**
     * @param wsTransactionType $wSTransactionType
     * @param string $commerceId
     * @param string $buyOrder
     * @param string $sessionId
     * @param anyURI $returnURL
     * @param anyURI $finalURL
     * @param wsTransactionDetail[] $transactionDetails
     * @param wpmDetailInput $wPMDetail
     * @access public
     */
    public function __construct($wSTransactionType, $commerceId, $buyOrder, $sessionId, $returnURL, $finalURL, $transactionDetails, $wPMDetail)
    {
      $this->wSTransactionType = $wSTransactionType;
      $this->commerceId = $commerceId;
      $this->buyOrder = $buyOrder;
      $this->sessionId = $sessionId;
      $this->returnURL = $returnURL;
      $this->finalURL = $finalURL;
      $this->transactionDetails = $transactionDetails;
      $this->wPMDetail = $wPMDetail;
    }

}
