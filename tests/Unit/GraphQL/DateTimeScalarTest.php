<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL;

use DateTime;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use Monolog\Test\TestCase;
use Siler\GraphQL\DateScalar;
use Siler\GraphQL\DateTimeScalar;

class DateTimeScalarTest extends TestCase
{
    public function testDateSerialize()
    {
        $expected = '2020-07-18';
        $ds = new DateScalar();

        $actual = $ds->serialize(DateTime::createFromFormat(DateScalar::FORMAT, $expected));
        $this->assertSame($expected, $actual);

        $this->expectException(Error::class);
        $ds->serialize($expected);
    }

    public function testDateParse()
    {
        $value = '2020-07-18';
        $literal = new StringValueNode(['value' => $value]);

        $expected = DateTime::createFromFormat(DateScalar::FORMAT, $value);
        $ds = new DateScalar();

        $actual = $ds->parseLiteral($literal);
        $this->assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $ds->parseLiteral($value);

        $actual = $ds->parseValue($value);
        $this->assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $ds->parseValue('2020-07-18');
    }

    public function testDateTimeSerialize()
    {
        $expected = '2020-07-18 13:40:00';
        $dts = new DateTimeScalar();

        $actual = $dts->serialize(DateTime::createFromFormat(DateTimeScalar::FORMAT, $expected));
        $this->assertSame($expected, $actual);

        $this->expectException(Error::class);
        $dts->serialize($expected);
    }

    public function testDateTimeParse()
    {
        $value = '2020-07-18 13:40:00';
        $literal = new StringValueNode(['value' => $value]);

        $expected = DateTime::createFromFormat(DateTimeScalar::FORMAT, $value);
        $dts = new DateTimeScalar();

        $actual = $dts->parseLiteral($literal);
        $this->assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $dts->parseLiteral($value);

        $actual = $dts->parseValue($value);
        $this->assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $dts->parseValue('2020-07-18');
    }
}
