<?php
namespace Inteleon\Lekab;

use SoapClient;
use SoapVar;
use SoapHeader;
use SoapFault;
use Inteleon\Lekab\Exception\ClientException;
use Inteleon\Soap\Client as InteleonSoapClient;
use Inteleon\Soap\Exception\ClientException as InteleonSoapClientException;

abstract class Lekab
{
    /** @var string WSDL */
    protected $wsdl = '';

    /** @var SoapClient */
    protected $soap_client;

    /** @var string Lekab username */
    protected $username;

    /** @var string Lekab password */
    protected $password;

    /** @var int The number of milliseconds to wait while trying to connect. */
    protected $connect_timeout;

    /** @var int The maximum number of milliseconds to allow execution */
    protected $timeout;

    /** @var int Number of connect attempts to be made if connection error occurs */
    protected $connect_attempts;

    /** @var boolean Verify Lekab certificate */
    protected $verify_certificate;

    /** @var boolean Cache the WSDL */
    protected $cache_wsdl;

    /**
     * Constructor
     *
     * @param string $username Lekab username
     * @param string $password Lekab Password
     * @param integer $connect_timeout Connect timeout in milliseconds
     * @param integer $timeout Timeout in milliseconds
     * @param int $connect_attempts Number of connect attempts
     * @param boolean $verify_certificate Verify Lekab certificate
     * @param boolean $cache_wsdl Cache the WSDL
     */
    public function __construct($username, $password, $connect_timeout = 30000, $timeout = 30000, $connect_attempts = 1, $verify_certificate = true, $cache_wsdl = true)
    {
        $this->username = $username;
        $this->password = $password;
        $this->connect_timeout = $connect_timeout;
        $this->timeout = $timeout;
        $this->connect_attempts = $connect_attempts;
        $this->verify_certificate = $verify_certificate;
        $this->cache_wsdl = $cache_wsdl ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE;
    }

    /**
     * Set Soap Client. If you set a SoapClient then the options passed in the
     * constructor will be ignored.
     *
     * @param SoapClient $soap_client
     */
    public function setSoapClient(SoapClient $soap_client)
    {
        $this->soap_client = $soap_client;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
                'connect_timeout' => ($this->connect_timeout / 1000),
            ));
            $soap_client->setTimeout($this->timeout);
            $soap_client->setConnectTimeout($this->connect_timeout);
            $soap_client->setConnectAttempts($this->connect_attempts);
            $soap_client->setVerifyCertificate($this->verify_certificate);

            //Set headers
            $soap_header_data = new SoapVar('<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><wsse:UsernameToken wsu:Id="UsernameToken-3" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:Username>'.$this->getUsername().'</wsse:Username><wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->getPassword().'</wsse:Password></wsse:UsernameToken></wsse:Security>', XSD_ANYXML);
            $soap_header = new SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', $soap_header_data);
            $soap_client->__setSoapHeaders($soap_header);

            return $this->soap_client = $soap_client;

        } catch (SoapFault $sf) {

            throw new ClientException($this->soapFaultToString($sf));

        } catch (InteleonSoapClientException $e) {

            throw new ClientException('Connection error: ' . $e->getMessage());
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
