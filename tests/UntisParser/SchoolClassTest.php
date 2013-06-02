<?php

namespace UntisParser;

use UntisParser\SchoolClass;

require_once "UntisObjectTest.php";

class SchoolClassTest extends UntisObjectTest {

    public function testBasicClass() {
        $input = [
            '00K   ,afstud-M,,,"afstuderen",,,0,16777088,,,,,,0,410401703',
            'SP    ,im'];
        $schoolClass = new SchoolClass(implode("\n", $input));
        $this->assertEquals('afstud-M', $schoolClass->getId());
        $this->assertEquals('afstuderen', $schoolClass->getText());
        $this->assertEquals('im', $schoolClass->getDepartmentId());
    }

    public function testClassAvailability() {
        $input = [
            '00K   ,s21T,,,"S tilburg",,,0,65408,,,,,,0,409971439',
            'ZA ,                     22',
            'SP    ,P4',
            'ZT000000000000',
            'KL             0                       0'];
        $schoolClass = new SchoolClass(implode("\n", $input));
        $this->assertSame(0, $schoolClass->getStatusForHour(20));
        $this->assertSame(-2, $schoolClass->getStatusForHour(21));
    }

    public function testFontysClasses() {
        $this->doTestFile(__BASE__ . '/tests/Data/Classes.txt', '00K', 'SchoolClass');
    }

}
