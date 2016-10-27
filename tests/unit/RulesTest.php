<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\SoapService;
use subdee\soapserver\tests\unit\RulesSoapController;
use subdee\soapserver\WsdlGenerator;

/**
 * Class RulesTest
 * @package subdee\soapserver\tests
 */
class RulesTest extends Test
{
    /**
     * Test to see if we see all validators (and some get ignored,just like we want)
     */
    public function testValidatorsProcessor()
    {
        $wsdlGenerator = new WsdlGenerator();

        $validators = $wsdlGenerator->readValidators('subdee\soapserver\tests\models\RulesTestModel');

        $this->assertArrayHasKey('integerValue',$validators);
        $this->assertArrayHasKey('stringValue', $validators);

        $this->assertArrayHasKey('validator',$validators['integerValue'][0]);

        $this->assertEquals('trim',$validators['regExpValue'][0]['validator']);

        $this->assertEquals('/[a-z]*/i',$validators['regExpValue'][1]['parameters']['pattern']);

        $this->assertNotContains('InvalidValidator',$validators['regExpValue']);
    }

    /**
     * Test to see how the xml looks
     */
    public function testXml()
    {
        $controller = new RulesSoapController();
        $soapService = new SoapService($controller, 'http://wsdl-url/', 'http://test-url/');
        $wsdl = $soapService->generateWsdl();

        $xml = simplexml_load_string($wsdl);
        $this->assertTrue($xml instanceOf \SimpleXMLElement);
        $this->assertTrue((string) $xml->getName() === 'definitions');

        $rulesValue = $xml->xpath('//xsd:simpleType[@name="rulestestmodelIntegerValue"]');
        $this->assertTrue($rulesValue[0] instanceof \SimpleXMLElement);

        $tokenValue = $xml->xpath('//xsd:simpleType/xsd:token');
        $this->assertEquals('[a-z]*',$tokenValue[0]['value']);
    }
}