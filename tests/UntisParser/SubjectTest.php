<?php

namespace UntisParser;

use UntisParser\Subject;

class SubjectTest extends \PHPUnit_Framework_TestCase {

    public function testSubjectParsing() {
        $line = '00F   ,wpb21,"Web programming p3",,,,,,,,,N,,,0,404721910';
        $subject = new Subject($line);
        $this->assertEquals($subject->getId(), 'wpb21');
        $this->assertEquals($subject->getDescription(), 'Web programming p3');
    }

}
