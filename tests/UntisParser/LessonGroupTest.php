<?php

namespace UntisParser;

use UntisParser\LessonGroup;

require_once 'UntisObjectTest.php';

class LessonGroupTest extends UntisObjectTest {

    public function testBasicLessonGroup() {
        $input = [
            '00G   ,wk1,"week 1",,,,,,,,,,,,0,408211100',
            'GR ,20120423,20120715,X'
        ];

        $lessonGroup = new LessonGroup(implode("\n", $input));
        $this->assertEquals('wk1', $lessonGroup->getId());
        $this->assertEquals('week 1', $lessonGroup->getDescription());
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d', '2012-04-23'), $lessonGroup->getStartDate());
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d', '2012-07-15'), $lessonGroup->getEndDate());
        $this->assertSame([0], $lessonGroup->getWeeks());
    }

    public function testLessonGroupWithGaps() {
        $input = [
            '00G   ,wk12358,"week 1+2+3+5+8",,,,,,,,,,,,0,409201830',
            'GR ,,,XXXX X  X'
        ];
        $lessonGroup = new LessonGroup(implode("\n", $input));
        $this->assertEquals('wk12358', $lessonGroup->getId());
        $this->assertEquals('week 1+2+3+5+8', $lessonGroup->getDescription());
        $this->assertSame([0, 1, 2, 3, 5, 8], $lessonGroup->getWeeks());
    }

}
