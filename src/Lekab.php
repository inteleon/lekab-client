<?php
namespace Inteleon;

use SoapVar;
use SoapHeader;
use SoapFault;
use Exception;
use Inteleon\InteleonSoapClient;

abstract class Lekab
{
    protected $wsdl = '';

    protected $soap_client;
    
      
    protected $username;

    
    protected $password;
  
    protected $cache_wsdl;
    

    protected $connect_timeout;
    

    protected $timeout;

    protected $connect_attempts;
    

    protected $verify_certificate;
    
//TODO: jämför mot decidas
    public function __construct($username, $password, $cache_wsdl = true, $connect_timeout = 30000, $timeout = 30000)
    {
        $this->username = $username;
        $this->password = $password;
        $this->cache_wsdl = $cache_wsdl ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE;
        $this->connect_timeout = $connect_timeout;
        $this->timeout = $timeout;
    }

    /**
     * Get the Soap client
     *
     * @return SoapClient
     */
    public function getSoapClient()
    {   
        if (isset($this->soap_client)) {
            return $this->soap_client;
        }

        try {
            
            $soap_client = new InteleonSoapClient($this->wsdl, array(
                'exceptions' => true,
                'trace' => false,
                'cache_wsdl' => $this->cache_wsdl,
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,           
            ));     

//TODO: Ta bort
$soap_client->setVerifyCertificate(false);                
     
            //Set headers
            $soap_header_data = new SoapVar('<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><wsse:UsernameToken wsu:Id="UsernameToken-3" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:Username>'.$this->username.'</wsse:Username><wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->password.'</wsse:Password></wsse:UsernameToken></wsse:Security>', XSD_ANYXML);
            $soap_header = new SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', $soap_header_data);  
            $soap_client->__setSoapHeaders($soap_header);

            return $this->soap_client = $soap_client;
            
        } catch (SoapFault $sf) {

            throw new Exception($this->soapFaultToString($sf)); 
                    
        } catch (InteleonSoapClientException $e) {

            throw new Exception('Connection error: ' . $e->getMessage());          
        }               
    }

    /**
     * Parse a LEKAB SoapFault to a string
     *
     * @param SoapFault $sf
     * @return string
     */
    protected function soapFaultToString(SoapFault $sf)
    {      
        $errorstring = $sf->faultcode . ': ' . $sf->faultstring;     
        if (isset($sf->detail->errorDetails) && $sf->detail->errorDetails) {        
            foreach ($sf->detail->errorDetails as $errorDetail) {
                $errorstring .= ' (' . $errorDetail->errorCode . ': ' . $errorDetail->errorDescription . ')';   
            }
        }
        return $errorstring;    
    }
}