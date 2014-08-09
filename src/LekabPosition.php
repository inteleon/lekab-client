<?php
namespace Inteleon;

use SoapFault;
use Inteleon\Exception\LekabClientException;
use Inteleon\Exception\InteleonSoapClientException;

class LekabPosition extends Lekab
{
    /** @var string WSDL */
    protected $wsdl = 'https://secure.lekab.com/ws/positioning.wsdl';
    
    /**
     * The Positioning operation is used to position a mobile station.
     *
     * @param string $consumerId The MSISDN of the mobile station to be positioned. Must be the mobile phonenumber including country code e.g. 46706352602.
     * @param string $referenceId The id of the incoming MO message.
     * @return array
     */
    public function positioning($consumerId, $referenceId)
    {
        $request = array(
            'PositioningRequest' => array(
                'consumerId' => $consumerId,
                'referenceId' => $referenceId,
            )
        );

        try {       
            $soap_client = $this->getSoapClient();
            $response = $soap_client->__soapCall('Positioning', $request);

        } catch (SoapFault $sf) {
            
            throw new LekabClientException($this->soapFaultToString($sf));

        } catch (InteleonSoapClientException $e) {

            throw new LekabClientException('Connection error: ' . $e->getMessage());          
        }

        return array(
            'latitude'  => $response->latitude,
            'longitude' => $response->longitude,
            'accuracy'  => $response->accuracy //Meters
        );  
    } 
}