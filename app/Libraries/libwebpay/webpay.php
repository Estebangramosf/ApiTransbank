<?php

namespace App\Libraries\libwebpay;

/* Importing webpay classes */
use App\Libraries\libwebpay\configuration;





use App\Libraries\libwebpay\webpay_normal;
use App\Libraries\libwebpay\webpay_mall_normal;
use App\Libraries\libwebpay\webpay_nullify;
use App\Libraries\libwebpay\webpay_capture;
use App\Libraries\libwebpay\webpay_oneclick;
use App\Libraries\libwebpay\webpay_complete;

use App\Libraries\libwebpay\soap_wsse;
use App\Libraries\libwebpay\soap_validation;
use App\Libraries\libwebpay\soapclient;

use App\Libraries\libwebpay\WebPayNormal;

/**
 * @author     Allware Ltda. (http://www.allware.cl)
 * @copyright  2015 Transbank S.A. (http://www.tranbank.cl)
 * @date       Jan 2015
 * @license    GNU LGPL
 * @version    2.0.1
 */

require_once(__DIR__ . '/soap_wsse.php');
require_once(__DIR__ . '/soap_validation.php');
require_once(__DIR__ . '/soapclient.php');

//include('configuration.php');
include('webpay_normal.php');
include('webpay_mall_normal.php');
include('webpay_nullify.php');
include('webpay_capture.php');
include('webpay_oneclick.php');
include('webpay_complete.php');

class Webpay {


    var $configuration, $webpayNormal, $webpayMallNormal, $webpayNullify, $webpayCapture, $webpayOneClick, $webpayCompleteTransaction;

    function __construct($params) {

        $this->configuration = $params;
    }

  /**
   * @return \App\Libraries\libwebpay\WebPayNormal
   */
    public function getNormalTransaction() {
        if ($this->webpayNormal == null) {
            $this->webpayNormal = new WebPayNormal($this->configuration);
        }
        return $this->webpayNormal;
    }

    public function getMallNormalTransaction() {
        if ($this->webpayMallNormal == null) {
            $this->webpayMallNormal = new WebPayMallNormal($this->configuration);
        }
        return $this->webpayMallNormal;
    }

    public function getNullifyTransaction() {
        if ($this->webpayNullify == null) {
            $this->webpayNullify = new WebpayNullify($this->configuration);
        }
        return $this->webpayNullify;
    }

    public function getCaptureTransaction() {
        if ($this->webpayCapture == null) {
            $this->webpayCapture = new WebpayCapture($this->configuration);
        }
        return $this->webpayCapture;
    }

    public function getOneClickTransaction() {
        if ($this->webpayOneClick == null) {
            $this->webpayOneClick = new WebpayOneClick($this->configuration);
        }
        return $this->webpayOneClick;
    }

    public function getCompleteTransaction() {
        if ($this->webpayCompleteTransaction == null) {
            $this->webpayCompleteTransaction = new WebpayCompleteTransaction($this->configuration);
        }
        return $this->webpayCompleteTransaction;
    }

}

class baseBean {
    
}

class getTransactionResult {

    var $tokenInput; //string

}

class getTransactionResultResponse {

    var $return; //transactionResultOutput

}
