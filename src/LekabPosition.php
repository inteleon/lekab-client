<?php
namespace Inteleon;


use SoapFault;
use Exception;

class LekabPosition
{
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
        try {       
            $request = array(
                'PositioningRequest' => array(
                    'consumerId' => $consumerId,
                    'referenceId' => $referenceId,
                )
            );

            $soap_client = $this->getSoapClient();

            $response = $soap_client->__soapCall('Positioning', $request);

        } catch (SoapFault $sf) {
            throw new Exception($this->soapFaultToString($sf)); 
        } catch (Exception $e) {    
            throw new Exception($e->getMessage());          
        }

        return array(
            'latitude'  => $response->latitude,
            'longitude' => $response->longitude,
            'accuracy' => $response->accuracy //meters
        );  
    } 
}