<?php
namespace Inteleon;

use SoapFault;
use Inteleon\Exception\LekabClientException;
use Inteleon\Exception\InteleonSoapClientException;

class LekabMessaging extends Lekab
{
    protected $wsdl = 'https://secure.lekab.com/ws/messaging-v2.wsdl';


    
    /**
     * Send a text message
     *
     * @param string $message (utf-8)
     * @param array|int $recipient (max 1000)
     * @param string|int $sender (utf-8)
     * @param boolean $replyable
     * @param array $options
     * @return array
     */
    public function send($message, $recipient, $sender, $replyable, $options = array())
    {
        //Mandatory parameters 
        $request = array(
            'SendRequest' => array(
                'recipients' => array(
                    'recipient' => is_array($recipient) ? $recipient : array($recipient)
                ),
                'sender' => $sender,
//TODO: KONTROLLERA
                'replyable' => ($replyable ? 'true' : 'false'),
                'data' => array(
                    'sms' => array(
                        'payload' => array(
                            'message' => $message
                        )
                    )
                )
            )
        );
        
        //Set priority (One of: Normal High Low)
        if (isset($options['priority'])) {
            $request['SendRequest']['priority'] = $options['priority'];
        }

        //User supplied conversation id. If the message is replyable the reply will get this conversationId.
        if (isset($options['conversationId'])) {
            $request['SendRequest']['conversationId'] = $options['conversationId'];
        }

        //The time for scheduled delivery. Must be in the future.
        if (isset($options['scheduledDelivery'])) {
            $request['SendRequest']['scheduledDelivery'] = $options['scheduledDelivery'];
        }

        //The time a message is valid to before being removed from the send queue. Must be in the future.
        if (isset($options['validTo'])) {
            $request['SendRequest']['validTo'] = $options['validTo'];
        }

        //Set flash message
        if (isset($options['flash'])) {
            $request['SendRequest']['data']['sms']['flash'] = ($options['flash'] ? 'true' : 'false');
        }
        
        try {
            $soap_client = $this->getSoapClient();
            $response = $soap_client->__soapCall('Send',$request);

        } catch (SoapFault $sf) {
            throw new LekabClientException($this->soapFaultToString($sf)); 
                    
        } catch (InteleonSoapClientException $e) {    
            throw new LekabClientException("Connection error: " . $e->getMessage());          
        }
        

print_r($response);


        $result = array();

        foreach ($response->messageStatus as $messageStatus) {
            $result[] = array(
                'statusCode'            => $messageStatus->statusCode,
                'statusText'            => $messageStatus->statusText,
                'id'                    => $messageStatus->id,
                'sender'                => $messageStatus->sender,
                'recipient'             => $messageStatus->recipient,
                'time'                  => $messageStatus->time,
                'billingStatus'         => $messageStatus->billingStatus,
                'NumberOfMessages'      => $messageStatus->attributes->attribute[0]->value->integer,
                'NumberOfCharacters'    => $messageStatus->attributes->attribute[1]->value->integer                
            );
        }

        return $result; 
    }   
    
    /**
     * Get status information of a sent text message
     *
     * @param boolean $markStatusesRead
     * @param int $maxNumberOfMessages
     * @param array|int $messageIds
     * @return array
     */
    public function getStatus($markStatusesRead = true, $maxNumberOfStatuses = 5, $messageIds = null)
    {
        if ($messageIds) {
            $messageIds = is_array($messageIds) ? $messageIds : array($messageIds); 
        }
        
        try {               
            $request = array('GetMessageStatusRequest' => array('markStatusesRead' => $markStatusesRead, 'maxNumberOfStatuses' => $maxNumberOfStatuses));           
            
            if ($messageIds) {
                foreach($messageIds as $messageId) {
                    $request['GetMessageStatusRequest']['messageIds']['messageId'][] = $messageId;
                }
            }
                    
            $soap_client = $this->getSoapClient();
            
            $response = $soap_client->__soapCall('GetMessageStatus',$request);

        } catch (SoapFault $sf) {
            throw new Exception($this->soapFaultToString($sf)); 
                    
        } catch (Exception $e) {    
            throw new Exception($e->getMessage());          
        }       
        
        if (isset($response->messageStatus) === false) {
            return null;
        }
                                    
        $result = array();
        foreach($response->messageStatus as $messageStatus) {
            $result[] = array(
                'statusCode'            => $messageStatus->statusCode,
                'statusText'            => $messageStatus->statusText,
                'id'                    => $messageStatus->id,
                'sender'                => $messageStatus->sender,
                'recipient'             => $messageStatus->recipient,
                'time'                  => $messageStatus->time,
                'billingStatus'         => $messageStatus->billingStatus,
                'NumberOfMessages'      => $messageStatus->attributes->attribute[0]->value->integer,
                'NumberOfCharacters'    => $messageStatus->attributes->attribute[1]->value->integer
            );
        }
        return $result;
    }

    /**
     * Get inbox
     *
     * @param boolean $markMessagesRead
     * @param int $maxNumberOfMessages
     * @param boolean $retrieveMessages
     * @param int|array $messageIds
     * @return array
     */
    public function get($markMessagesRead = true, $maxNumberOfMessages = 5, $retrieveMessages = true, $messageIds = null)
    {
        if ($messageIds) {
            $messageIds = is_array($messageIds) ? $messageIds : array($messageIds); 
        }
        
        try {           
            $request = array('GetIncomingMessagesRequest' => array('markMessagesRead' => $markMessagesRead, 'maxNumberOfMessages' => $maxNumberOfMessages, 'retrieveMessages' => $retrieveMessages));
            
            if ($messageIds) {
                foreach($messageIds as $messageId) {
                    $request['GetIncomingMessagesRequest']['messageIds']['messageId'][] = $messageId;
                }
            }
            
            $soap_client = $this->getSoapClient();  

            $response = $soap_client->__soapCall('GetIncomingMessages',$request);
            
        } catch(SoapFault $sf) {
            throw new Exception($this->soapFaultToString($sf)); 
                    
        } catch(Exception $e) { 
            throw new Exception($e->getMessage());          
        }
        
        if (isset($response->incomingMessages) === false) {
            return NULL;
        }
        
        $result = array();
        foreach($response->incomingMessages as $incomingMessage) {
            $result[] = array(
                'id'        => $incomingMessage->id,
                'sender'    => $incomingMessage->sender,
                'recipient' => $incomingMessage->recipient,
                'timeStamp' => $incomingMessage->timeStamp,
                'message'   => $incomingMessage->payload->sms->message
            );
        }
        return $result; 
    }

    public static function getLekabStatusText($code)
    {
        switch (intval($code)) {
            case 0:
                return "QUEUED";
            case 1:
                return "SENT";
            case 2:
                return "DELIVERED";
            case 3:
                return "DELETED";
            case 4:
                return "EXPIRED";
            case 5:
                return "REJECTED";
            case 6:
                return "UNDELIVERABLE";
            case 7:
                return "ACCEPTED";
            case 8:
                return "ABSENT SUBSCRIBER";
            case 9:
                return "UNKNOWN SUBSCRIBER";
            case 10:
                return "INVALID DESTINATION";
            case 11:
                return "SUBSCRIBER ERROR";
            case 12:
                return "UNKNOWN";
            case 13:
                return "ERROR";
            default:
                return "(UNKNOWN CODE?)";
        }
    }
    
    public static function getLekabStatusDesc($code)
    {
        switch (intval($code)) {
            case 0:
                return "Queued for delivery";
            case 1:
                return "Sent to operator";
            case 2:
                return "Delivered to the mobile station";
            case 3:
                return "The message was deleted";
            case 4:
                return "The message has expired";
            case 5:
                return "The message was rejected by the operator";
            case 6:
                return "The message could not be delivered";
            case 7:
                return "The message was accepted by the operator";
            case 8:
                return "The subscribers mobile station is switched off";
            case 9:
                return "The subscriber is not known";
            case 10:
                return "The destination address is invalid";
            case 11:
                return "The mobile station can not receive the message";
            case 12:
                return "The status of the message is unknown";
            case 13:
                return "Internal error when sending the message";
            default:
                return "(UNKNOWN CODE?)";
        }
    }           
}