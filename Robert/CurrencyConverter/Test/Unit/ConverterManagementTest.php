<?php

namespace Robert\CurrencyConverter\Test\Unit;

use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Robert\CurrencyConverter\Exception\BadApiResponseException;
use Robert\CurrencyConverter\Exception\InvalidCurrencyValueException;
use Robert\CurrencyConverter\Model\ConverterManagement;

class ConverterManagementTest extends TestCase
{
    /**
     * @var ConverterManagement
     */
    private $converterManagement;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    public function setUp()
    {
        $this->httpClientMock = $this->getMockBuilder(ClientInterface::class)
            ->getMockForAbstractClass();
        $this->serializer = $this->createMock(Json::class);
        $objectManager = new ObjectManager($this);
        $this->converterManagement = $objectManager->getObject(
            ConverterManagement::class,
            [
                'curlClient' => $this->httpClientMock,
                'json' => $this->serializer
            ]
        );
    }

    /**
     * @dataProvider provideNotPositiveIntegers
     */
    public function testItDoesNotAllowNotPositiveIntegerAsCurrencyValue($currencyValue)
    {
        $currencyFrom = 'USD';
        $currencyTo = 'PLN';
        $this->expectException(InvalidCurrencyValueException::class);
        $this->expectExceptionMessage('Sorry, currency must be a positive number, try again');

        $this->converterManagement->convert($currencyValue, $currencyFrom, $currencyTo);
    }

    /**
     * @dataProvider provideNotPositiveIntegers
     */
    public function testNoJsonApiResponseThrowsBadApiResponseException($returnValue)
    {
        $currencyFrom = 'USD';
        $currencyTo = 'PLN';
        $currencyValue = 1;

        $this->expectException(BadApiResponseException::class);
        $this->expectExceptionMessage('Converter Api response is not valid');

        $this->httpClientMock->expects($this->once())
            ->method('get');
        $this->httpClientMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($returnValue));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $this->converterManagement->convert($currencyValue, $currencyFrom, $currencyTo);
    }

    /**
     * @dataProvider provideTestValues
     */
    public function testConvertedValue($currencyValue, $returnApiValue, $result)
    {
        $currencyFrom = 'USD';
        $currencyTo = 'PLN';
        $this->httpClientMock->expects($this->once())
            ->method('get');
        $this->httpClientMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($returnApiValue));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $this->assertEquals($result, $this->converterManagement->convert($currencyValue, $currencyFrom, $currencyTo));
    }

    public function provideTestValues()
    {
        return [
            [1, '{"USD_PLN": 1.2}', 1*1.2],
            [1, '{"USD_PLN": 1.5}', 1*1.5],
            [1, '{"USD_PLN": 2}', 1*2],
        ];
    }

    public function provideNotPositiveIntegers()
    {
        return [
            [-1], ['a'], [[]], [null], ['.'], [new \StdClass], [true]
        ];
    }

    public function provideNotJSON()
    {
        return [
            [11], ['test'], [[]], [null], ['.'], [new \StdClass], [false]
        ];
    }
}
