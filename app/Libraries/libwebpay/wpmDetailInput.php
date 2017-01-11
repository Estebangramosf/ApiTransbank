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

class wpmDetailInput {

    /**
     * @var string $serviceId
     * @access public
     */
    public $serviceId = null;

    /**
     * @var string $cardHolderId
     * @access public
     */
    public $cardHolderId = null;

    /**
     * @var string $cardHolderName
     * @access public
     */
    public $cardHolderName = null;

    /**
     * @var string $cardHolderLastName1
     * @access public
     */
    public $cardHolderLastName1 = null;

    /**
     * @var string $cardHolderLastName2
     * @access public
     */
    public $cardHolderLastName2 = null;

    /**
     * @var string $cardHolderMail
     * @access public
     */
    public $cardHolderMail = null;

    /**
     * @var string $cellPhoneNumber
     * @access public
     */
    public $cellPhoneNumber = null;

    /**
     * @var dateTime $expirationDate
     * @access public
     */
    public $expirationDate = null;

    /**
     * @var string $commerceMail
     * @access public
     */
    public $commerceMail = null;

    /**
     * @var boolean $ufFlag
     * @access public
     */
    public $ufFlag = null;

    /**
     * @param string $serviceId
     * @param string $cardHolderId
     * @param string $cardHolderName
     * @param string $cardHolderLastName1
     * @param string $cardHolderLastName2
     * @param string $cardHolderMail
     * @param string $cellPhoneNumber
     * @param dateTime $expirationDate
     * @param string $commerceMail
     * @param boolean $ufFlag
     * @access public
     */
    public function __construct($serviceId, $cardHolderId, $cardHolderName, $cardHolderLastName1, $cardHolderLastName2, $cardHolderMail, $cellPhoneNumber, $expirationDate, $commerceMail, $ufFlag) {
        $this->serviceId = $serviceId;
        $this->cardHolderId = $cardHolderId;
        $this->cardHolderName = $cardHolderName;
        $this->cardHolderLastName1 = $cardHolderLastName1;
        $this->cardHolderLastName2 = $cardHolderLastName2;
        $this->cardHolderMail = $cardHolderMail;
        $this->cellPhoneNumber = $cellPhoneNumber;
        $this->expirationDate = $expirationDate;
        $this->commerceMail = $commerceMail;
        $this->ufFlag = $ufFlag;
    }

}
