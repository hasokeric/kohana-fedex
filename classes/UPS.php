<?php

class UPS {

    protected $_service;
    protected $_currency;
    protected static $_instance = array();
    protected $_methods = array();
    protected $_base_request = array();
    protected $_last_request;

    public static function getServices()
    {
        $service_config = Kohana::$config->load('ups.service');

        return array_keys($service_config);
    }

    public static function __callStatic($name, $arguments)
    {
        $service = $name;
        $currency = $arguments[0];

        if ( ! isset(UPS::$_instance[$service][$currency]))
        {
            $currency_config = Kohana::$config->load('ups.currency.'.$currency);
            $service_config = Kohana::$config->load('ups.service.'.$service);

            if (empty($service_config))
                throw new Kohana_Exception('The UPS Service has not been selected');

            if (empty($currency_config))
                throw new Kohana_Exception('The UPS account currency has not been selected');

            UPS::$_instance[$service][$currency] = new UPS($service_config, $currency_config);
        }

        return UPS::$_instance[$service][$currency];
    }

    private function __construct($service, $currency)
    {
        $this->_service = $service;
        $this->_currency = $currency;

        $wsdl = Kohana::$config->load('ups.wsdldirectory').$this->_service['wsdl'];

        $soap_options = array();
        $soap_options['soap_version'] = 'SOAP_1_1';
        $soap_options['trace'] = true;
        $soap_options['exceptions'] = true;
        if (Kohana::$environment === Kohana::PRODUCTION) $soap_options['cache_wsdl'] = WSDL_CACHE_NONE;

        $this->_soapClient = new SoapClient($wsdl, $soap_options);
        $this->_soapClient->__setLocation($this->_currency['location'].$this->_service['location']);

        //create soap header

        $upss['UsernameToken'] = array(
            'Username' => $this->_currency['Username'],
            'Password' => $this->_currency['Password'],
        );
        $upss['ServiceAccessToken'] = array(
            'AccessLicenseNumber' => $this->_currency['AccessLicenseNumber'],
        );

        $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0','UPSSecurity',$upss);
        $this->_soapClient->__setSoapHeaders($header);

        $methods = $this->_soapClient->__getFunctions();
        foreach ($methods as $method)
        {
            preg_match('/ ([a-zA-Z]+)\(/', $method, $matches);
            $this->_methods[] = $matches[1];
        }
    }

    public function getServiceMethods()
    {
        return $this->_methods;
    }

    public function getLastRequest()
    {
        return $this->_last_request;
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, $this->_methods))
        {

            $this->_last_request = $arguments[0];
            try
            {
                $response = $this->_soapClient->$name($this->_last_request);
            }
            catch (SoapFault $sf)
            {
                throw new Kohana_Exception('UPS SoapFault error: :message', array(':message' => $sf->getMessage()));
            }
            catch (Exception $e)
            {
                throw new Kohana_Exception('UPS Exception error: :message', array(':message' => $e->getMessage()));
            }

            return $response;
        }
    }
}
