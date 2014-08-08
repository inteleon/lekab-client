<?php
namespace Inteleon;

use Framework\Misc\SoapClientCurl;
use SoapVar;
use SoapHeader;
use SoapFault;
use Exception;


abstract class Lekab
{
    protected $wsdl = '';

    protected $soap_client;
    
    
    protected $cache_wsdl;
    

    protected $connect_timeout;
    

    protected $timeout; 
    
    
    protected $username;

    
    protected $password;
    

    public function __construct($username, $password, $cache_wsdl = true, $connect_timeout = 30000, $timeout = 30000)
    {
        $this->username = $username;
        $this->password = $password;
        $this->cache_wsdl = $cache_wsdl ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE;
        $this->connect_timeout = $connect_timeout;
        $this->timeout = $timeout;
    }

    protected function getSoapClient()
    {   
        if (isset($this->soap_client)) {
            return $this->soap_client;
        }

        try {
            $wss_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
            $wss_header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><wsse:UsernameToken wsu:Id="UsernameToken-3" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:Username>'.$this->username.'</wsse:Username><wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->password.'</wsse:Password></wsse:UsernameToken></wsse:Security>';
            
            $soap_client = new SoapClientCurl('https://secure.infowireless.com/ws/messaging.wsdl', array(
                'exceptions' => true,
                'trace' => false,
                'cache_wsdl' => $this->cache_wsdl,
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                'connection_timeout' => 30,
                'curlopts' => array(
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSLVERSION => 3,
                    CURLOPT_TIMEOUT_MS => $this->timeout,
                    CURLOPT_CONNECTTIMEOUT_MS => $this->connect_timeout,
                ),              
            ));     
                        
            //Set wsse headers (XML)
            $soap_var = new SoapVar($wss_header, XSD_ANYXML);
            $soap_header = new SoapHeader($wss_ns, "Security", $soap_var);  
            $soap_client->__setSoapHeaders($soap_header);   
    
            return $this->soap_client = $soap_client;
            
        } catch (SoapFault $sf) {
            throw new Exception($this->soapFaultToString($sf)); 
                    
        } catch (Exception $e) {    
            throw new Exception($e->getMessage());          
        }               
    }

    protected function soapFaultToString(SoapFault $sf)
    {       
        $errorstring = '['.$sf->faultcode.'] '.$sf->faultstring.'';     
        if ($sf->detail->errorDetails) {        
            foreach($sf->detail->errorDetails as $errorDetail) {
                $errorstring .= ' ('.$errorDetail->errorCode.': '.$errorDetail->errorDescription.')';   
            }
        }
        return $errorstring;    
    }

}