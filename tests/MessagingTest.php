<?php

use Inteleon\Lekab\Messaging;

class LekabMessagingTest extends PHPUnit_Framework_TestCase
{
    protected function createSoapClientMock($response)
    {
        $soap_client_mock = $this->getMockBuilder('SoapClient')
                     ->setMethods(array('__soapCall'))
                     ->disableOriginalConstructor()
                     ->getMock();

        $soap_client_mock->expects($this->any())
             ->method('__soapCall')
             ->will($this->returnValue($response));

        return $soap_client_mock;
    }

    public function testSend()
    {
        $dummy_lekab_response = unserialize('O:8:"stdClass":1:{s:13:"messageStatus";a:1:{i:0;O:8:"stdClass":8:{s:10:"statusCode";i:0;s:10:"statusText";s:6:"QUEUED";s:2:"id";s:10:"0-00000000";s:6:"sender";s:4:"xxxx";s:9:"recipient";s:11:"00000000000";s:4:"time";s:29:"2014-08-28T21:10:05.146+02:00";s:13:"billingStatus";i:0;s:10:"attributes";O:8:"stdClass":1:{s:9:"attribute";a:2:{i:0;O:8:"stdClass":2:{s:4:"name";s:16:"NumberOfMessages";s:5:"value";O:8:"stdClass":1:{s:7:"integer";i:1;}}i:1;O:8:"stdClass":2:{s:4:"name";s:18:"NumberOfCharacters";s:5:"value";O:8:"stdClass":1:{s:7:"integer";i:4;}}}}}}}');
        $soap_client_mock = $this->createSoapClientMock($dummy_lekab_response);

        $messaging = new Messaging(null, null);
        $messaging->setSoapClient($soap_client_mock);
        $result = $messaging->send(null, null, null, null, null, array());

        $this->assertArrayHasKey('statusCode', $result[0]);
        $this->assertArrayHasKey('statusText', $result[0]);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('sender', $result[0]);
        $this->assertArrayHasKey('recipient', $result[0]);
        $this->assertArrayHasKey('time', $result[0]);
        $this->assertArrayHasKey('billingStatus', $result[0]);
        $this->assertArrayHasKey('NumberOfMessages', $result[0]);
        $this->assertArrayHasKey('NumberOfCharacters', $result[0]);
    }

    public function testStatus()
    {
        $dummy_lekab_response = unserialize('O:8:"stdClass":1:{s:13:"messageStatus";a:1:{i:0;O:8:"stdClass":8:{s:10:"statusCode";i:2;s:10:"statusText";s:9:"DELIVERED";s:2:"id";s:10:"00-0000000";s:6:"sender";s:4:"xxxx";s:9:"recipient";s:11:"00000000000";s:4:"time";s:29:"2014-08-28T21:10:08.000+02:00";s:13:"billingStatus";i:0;s:10:"attributes";O:8:"stdClass":1:{s:9:"attribute";a:2:{i:0;O:8:"stdClass":2:{s:4:"name";s:16:"NumberOfMessages";s:5:"value";O:8:"stdClass":1:{s:7:"integer";i:1;}}i:1;O:8:"stdClass":2:{s:4:"name";s:18:"NumberOfCharacters";s:5:"value";O:8:"stdClass":1:{s:7:"integer";i:4;}}}}}}}');
        $soap_client_mock = $this->createSoapClientMock($dummy_lekab_response);

        $messaging = new Messaging(null, null);
        $messaging->setSoapClient($soap_client_mock);
        $result = $messaging->send(null, null, array());

        $this->assertArrayHasKey('statusCode', $result[0]);
        $this->assertArrayHasKey('statusText', $result[0]);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('sender', $result[0]);
        $this->assertArrayHasKey('recipient', $result[0]);
        $this->assertArrayHasKey('time', $result[0]);
        $this->assertArrayHasKey('billingStatus', $result[0]);
        $this->assertArrayHasKey('NumberOfMessages', $result[0]);
        $this->assertArrayHasKey('NumberOfCharacters', $result[0]);
    }

    public function testGet()
    {
        $dummy_lekab_response = unserialize('O:8:"stdClass":1:{s:16:"incomingMessages";a:1:{i:0;O:8:"stdClass":5:{s:2:"id";s:9:"0-0000000";s:6:"sender";s:11:"00000000000";s:9:"recipient";s:11:"00000000000";s:9:"timeStamp";s:29:"2014-08-28T21:31:49.000+02:00";s:7:"payload";O:8:"stdClass":1:{s:3:"sms";O:8:"stdClass":1:{s:7:"message";s:7:"xxxxxxx";}}}}}');
        $soap_client_mock = $this->createSoapClientMock($dummy_lekab_response);

        $messaging = new Messaging(null, null);
        $messaging->setSoapClient($soap_client_mock);
        $result = $messaging->get(null, null, null, array());

        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('sender', $result[0]);
        $this->assertArrayHasKey('recipient', $result[0]);
        $this->assertArrayHasKey('timeStamp', $result[0]);
        $this->assertArrayHasKey('message', $result[0]);
    }

    public function testSetOptions()
    {
        $messaging = new Messaging('user', 'pass', 1000, 2000, 3, false, false);
        $this->assertEquals('user', PHPUnit_Framework_Assert::readAttribute($messaging, 'username'));
        $this->assertEquals('pass', PHPUnit_Framework_Assert::readAttribute($messaging, 'password'));
        $this->assertEquals(1000, PHPUnit_Framework_Assert::readAttribute($messaging, 'connect_timeout'));
        $this->assertEquals(2000, PHPUnit_Framework_Assert::readAttribute($messaging, 'timeout'));
        $this->assertEquals(3, PHPUnit_Framework_Assert::readAttribute($messaging, 'connect_attempts'));
        $this->assertEquals(false, PHPUnit_Framework_Assert::readAttribute($messaging, 'verify_certificate'));
        $this->assertEquals(false, PHPUnit_Framework_Assert::readAttribute($messaging, 'cache_wsdl'));
    }
}
