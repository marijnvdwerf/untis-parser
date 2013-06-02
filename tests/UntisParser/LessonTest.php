<?php

namespace UntisParser;

use UntisParser\Lesson;

class LessonTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @var \UntisParser\Schedule
     */
    public $schedule;

    public function setUp() {
        $input = [
            'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1',
            'AA58      ,845~935,935~1025,1045~1135,1135~1225,1225~1315,1315~1405,1405~1455,1515~1605,1605~1655,1655~1745,1800~1850,1850~1940,2000~2050,2050~2140',
            '00G   ,wk9,"week9",,,,,,,,,,,,0,409211102',
            'GR ,20120423,20120715,         X'
        ];
        $this->schedule = new \UntisParser\Schedule(implode("\n", $input));
    }

    public function testLesson() {
        $input = [
            '0U ,3238,wk9,wk9,"raaijmakers.g",,,,,,,,,aBn,,,,3238,410501248',
            'UN2                  2',
            'Uf ,,voordracht',
            'Uk ,,afstud-M',
            'Ul ,,von,,wijn',
            'Ur ,,R1_4.18',
            'Uz1 1 1 1 1           n',
            'Uz2 1 1 1 1           n'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $this->assertSame(3238, $lesson->getId());
        $this->assertSame('raaijmakers.g', $lesson->getText());
        $this->assertSame(['voordracht'], $lesson->getSubjectIds());
        $this->assertSame(['afstud-M'], $lesson->getSchoolClassIds());
        $this->assertSame(['von', 'wijn'], $lesson->getTeacherIds());
        $this->assertSame(['R1_4.18'], $lesson->getRoomIds());
    }

    public function testLessonMoments() {
        $input = [
            '0U ,3238,wk9,wk9,"raaijmakers.g",,,,,,,,,aBn,,,,3238,410501248',
            'UN2                  2',
            'Uf ,,voordracht',
            'Uk ,,afstud-M',
            'Ul ,,von,,wijn',
            'Ur ,,R1_4.18',
            'Uz1 1 1 1 1           n',
            'Uz2 1 1 1 1           n'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $moments = $lesson->getMoments();

        $this->assertCount(1, $moments);

        $this->assertSame(['von', 'wijn'], $moments[0]->getTeacherIds());
        $this->assertSame(['afstud-M'], $moments[0]->getSchoolClassIds());
    }

    public function testAdvancedLessonMoments() {
        $input = [
            '0U ,2265,wk3,wk3,"inno geb",,,0,4227327,,,,,aBfn,,,,0,410231100',
            'UN2                  2',
            'Uf ,,voorlichting',
            'Uk ,,s21T,,s21,,s22,,s23,,s24,,s25v,,b21T,,b21,,b22,,m21T,,m21,,m22,,m23,,m24,,m25,,t21,,t21T',
            'Ul ,,slas,,slk',
            'Ur ,,R1_3.13,,R1_1.01',
            'Uz1 1 6 1 1           n',
            'Uz2 1 6 1 1           n',
            'Uz1 7 9 1 1           n',
            'Uz2 7 9 1 1           n',
            'Uz1 10171 2           n',
            'Uz2 10171 2           n'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $moments = $lesson->getMoments();

        $this->assertCount(2, $moments);

        $this->assertSame(['slas', 'slk'], $moments[0]->getTeacherIds());
        $this->assertSame('R1_3.13', $moments[0]->getRoomId());
        $this->assertCount(9, $moments[0]->getSchoolClassIds());

        $this->assertSame(['slas', 'slk'], $moments[1]->getTeacherIds());
        $this->assertSame('R1_1.01', $moments[1]->getRoomId());
        $this->assertCount(8, $moments[1]->getSchoolClassIds());
    }

    public function testMomentLines() {
        $input = [
            '0U ,3503,wk10,wk10,,,,,,,,,,Bn,,,,3503,410501231',
            'UN1                  1',
            'Uf ,,k-assess',
            'Uk ,,M-stage',
            'Ul ,,gms',
            'Ur ,,R1_4.61b',
            'Uz1 1 1 1 1            ,"huijsmans.b"',
            'Uz1 1 1 1 1            ,"tan.t."'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $moments = $lesson->getMoments();

        $this->assertSame(['huijsmans.b'], $moments[0]->getLines());
        $this->assertSame(['tan.t.'], $moments[1]->getLines());
    }

    public function testColors() {
        $input = '0U ,236,,,"herk",,,16777215,255,,,,,aBfn,,,,236,409281203';
        $lesson = new Lesson($this->schedule, $input);
        $this->assertSame('#ffffff', $lesson->getForegroundColour()->getHex());
        $this->assertSame('#ff0000', $lesson->getBackgroundColour()->getHex());
    }

    public function testSlotParsing() {
        $input = 'UZ00 ,4/5,,R1_3.96,R1_3.97';
        $slots = \UntisParser\LessonSlot::parse($input);

        $this->assertSame(3, $slots[0]->getDay());
        $this->assertSame(4, $slots[0]->getStartHour());
        $this->assertSame(4, $slots[0]->getEndHour());
        $this->assertSame('R1_3.96', $slots[0]->getRoomId());

        $this->assertSame(3, $slots[1]->getDay());
        $this->assertSame(4, $slots[1]->getStartHour());
        $this->assertSame(4, $slots[1]->getEndHour());
        $this->assertSame('R1_3.97', $slots[1]->getRoomId());
    }

    public function testSlots() {
        $input = [
            '0U ,3762,wk9,wk9,"th",,,0,255,,,,,aBfn,,,,3762,410781708',
            'UZ00 ,4/5,,R1_3.96,R1_3.97'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $slots = $lesson->getSlots();

        $this->assertSame(3, $slots[0]->getDay());
        $this->assertSame(4, $slots[0]->getStartHour());
        $this->assertSame(4, $slots[0]->getEndHour());
        $this->assertSame('R1_3.96', $slots[0]->getRoomId());

        $this->assertSame(3, $slots[1]->getDay());
        $this->assertSame(4, $slots[1]->getStartHour());
        $this->assertSame(4, $slots[1]->getEndHour());
        $this->assertSame('R1_3.97', $slots[1]->getRoomId());
    }

    public function testMergingSlots() {
        $input = [
            '0U ,3762,wk9,wk9,"th",,,0,255,,,,,aBfn,,,,3762,410781708',
            'UZ00 ,4/5,,R1_3.96',
            'UZ00 ,4/6,,R1_3.96',
            'UZ00 ,4/7,,R1_3.96',
        ];

        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $slots = $lesson->getSlots();

        $this->assertCount(1, $slots);

        $this->assertSame(3, $slots[0]->getDay());
        $this->assertSame(4, $slots[0]->getStartHour());
        $this->assertSame(6, $slots[0]->getEndHour());
        $this->assertSame('R1_3.96', $slots[0]->getRoomId());
    }

    public function testMergingSlotsWrongOrder() {
        $input = [
            '0U ,3762,wk9,wk9,"th",,,0,255,,,,,aBfn,,,,3762,410781708',
            'UZ00 ,4/6,,R1_3.96',
            'UZ00 ,4/7,,R1_3.96',
            'UZ00 ,4/5,,R1_3.96',
        ];

        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $slots = $lesson->getSlots();

        $this->assertCount(1, $slots);

        $this->assertSame(3, $slots[0]->getDay());
        $this->assertSame(4, $slots[0]->getStartHour());
        $this->assertSame(6, $slots[0]->getEndHour());
        $this->assertSame('R1_3.96', $slots[0]->getRoomId());
    }

    public function testMergingSlotsWithGap() {
        $input = [
            '0U ,3762,wk9,wk9,"th",,,0,255,,,,,aBfn,,,,3762,410781708',
            'UZ00 ,4/5,,R1_3.96',
            'UZ00 ,4/7,,R1_3.96',
        ];

        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $slots = $lesson->getSlots();

        $this->assertCount(2, $slots);

        $this->assertSame(3, $slots[0]->getDay());
        $this->assertSame(4, $slots[0]->getStartHour());
        $this->assertSame(4, $slots[0]->getEndHour());
        $this->assertSame('R1_3.96', $slots[0]->getRoomId());

        $this->assertSame(3, $slots[1]->getDay());
        $this->assertSame(6, $slots[1]->getStartHour());
        $this->assertSame(6, $slots[1]->getEndHour());
        $this->assertSame('R1_3.96', $slots[1]->getRoomId());
    }

    public function testNoSchoolClasses() {
        $input = [
            '0U ,453,,,"Sam Sanders",,,,,,,,,aBn,,,,453,404720959',
            'UN0 0, 0',
            'Uf ,,k-assess',
            'Ul,, hdn,, tns',
            'Ur ,,R1_3.19',
            'Uz1 1 1 n',
            'Uz2 1 0'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));

        $this->assertCount(0, $lesson->getSchoolClassIds());
        $moments = $lesson->getMoments();
        $this->assertCount(2, $moments);
        $this->assertSame(['hdn'], $moments[0]->getTeacherIds());
        $this->assertSame([], $moments[0]->getSchoolClassIds());
        $this->assertSame('k-assess', $moments[0]->getSubjectId());
        $this->assertSame('R1_3.19', $moments[0]->getRoomId());

        $this->assertSame(['tns'], $moments[1]->getTeacherIds());
        $this->assertSame([], $moments[1]->getSchoolClassIds());
        $this->assertSame('k-assess', $moments[1]->getSubjectId());
        $this->assertSame(null, $moments[1]->getRoomId());
        //$this->assertSame($lesson, $input)
    }

    public function testNoRoom() {
        $input = [
            '0U, 438,,,,,,,,,,,, aBn,,,, 438, 407221437',
            'UN0 0',
            'Uf,, terugkomdag',
            'Uk,, afstud-S',
            'Ul,, bon,, brk,, broe,, cts,, dor,, fbl,, hdn,, kpr,, Lz,, Lar,, okp,, ros,, slk,, srk,, vgl,, zms,, zls',
            'Ur,, R1',
            'Uz1 1 1 1 1',
            'Uz2 1 1 1 0',
            'Uz3 1 1 1 0'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));

        $moments = $lesson->getMoments();
        $this->assertCount(2, $moments);

        $this->assertSame('R1', $moments[0]->getRoomId());
        $this->assertSame(['bon'], $moments[0]->getTeacherIds());
        $this->assertSame('terugkomdag', $moments[0]->getSubjectId());
        $this->assertSame(['afstud-S'], $moments[0]->getSchoolClassIds());

        $this->assertSame(null, $moments[1]->getRoomId());
        $this->assertSame(['brk', 'broe'], $moments[1]->getTeacherIds());
        $this->assertSame('terugkomdag', $moments[1]->getSubjectId());
        $this->assertSame(['afstud-S'], $moments[1]->getSchoolClassIds());
    }

    public function testNoSubject() {
        $input = [
            '0U, 849, wk1-10, wk1-10,,,,,,,,,, aBn,,,, 849, 409281648',
            'UN13 13',
            'Uk,, lorentz',
            'Ul,, gts',
            'Uz1 1 1 0 0 n,,,,, 14216',
            'UZ10, 1/4'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $this->assertCount(0, $lesson->getSubjectIds());

        $moments = $lesson->getMoments();
        $this->assertCount(1, $moments);
        $this->assertSame(null, $moments[0]->getSubjectId());
        $this->assertSame(['gts'], $moments[0]->getTeacherIds());
        $this->assertSame(['lorentz'], $moments[0]->getSchoolClassIds());
    }

    public function testNoTeacher() {
        $input = [
            '0U ,2056,wk2,wk2,"rainier jansen",,,,,,,,,Bn,,,,0,412281243',
            'UN9                  9',
            'Uf ,,cursus',
            'Uk ,,incidenteel',
            'Ur ,,P1_C2.17,,P1_C2.23',
            'Uz0 1 1 1 1           n',
            'Uz0 1 1 1 2  0    0   n'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $this->assertCount(0, $lesson->getTeacherIds());

        $moments = $lesson->getMoments();
        $this->assertCount(2, $moments);

        $this->assertSame([], $moments[0]->getTeacherIds());
        $this->assertSame('cursus', $moments[0]->getSubjectId());
        $this->assertSame(['incidenteel'], $moments[0]->getSchoolClassIds());
        $this->assertSame('P1_C2.17', $moments[0]->getRoomId());

        $this->assertSame([], $moments[1]->getTeacherIds());
        $this->assertSame('cursus', $moments[1]->getSubjectId());
        $this->assertSame(['incidenteel'], $moments[1]->getSchoolClassIds());
        $this->assertSame('P1_C2.23', $moments[1]->getRoomId());
    }

    public function testMergingMoments() {
        $input = [
            '0U, 80, wk9, wk9, "besp",,,,,,,,, aBn,,,, 80, 409361719',
            'UN2 2',
            'Uf,, delta',
            'Uk,, m61,, m62,, m63',
            'Ul,, slas,, rui',
            'Ur,, R1_4.103',
            'Uz1 1 3 1 1,,,,, 12650',
            'Uz2 1 3 1 1,,,,, 12651',
            'UZ00, 4/1,, R1_4.103',
            'UZ00, 4/2,, R1_4.103'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $moments = $lesson->getMomentsForRoom('R1_4.103');

        $this->assertCount(1, $moments);
        $this->assertSame(['slas', 'rui'], $moments[0]->getTeacherIds());
        $this->assertSame(['m61', 'm62', 'm63'], $moments[0]->getSchoolClassIds());
        $this->assertSame('delta', $moments[0]->getSubjectId());
        $this->assertSame('R1_4.103', $moments[0]->getRoomId());
    }

    public function testMergingHours() {
        $input = [
            '0U, 80, wk9, wk9, "besp",,,,,,,,, aBn,,,, 80, 409361719',
            'UN2 2',
            'Uf,, delta',
            'Uk,, m61,, m62,, m63',
            'Ul,, slas,, rui',
            'Ur,, R1_4.103',
            'Uz1 1 3 1 1,,,,, 12650',
            'Uz2 1 3 1 1,,,,, 12651',
            'UZ00, 4/1,, R1_4.103',
            'UZ00, 4/2,, R1_4.103'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));
        $events = $lesson->getEvents();

        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertEquals(new \DateTime('2012-06-28T08:45:00'),
            $event->getStartDateTime());
        $this->assertEquals(new \DateTime('2012-06-28T10:25:00'),
            $event->getEndDateTime());
        $this->assertEquals(['slas', 'rui'], $event->getTeacherIds());
        $this->assertEquals(['m61', 'm62', 'm63'], $event->getSchoolClassIds());
        $this->assertEquals('R1_4.103', $event->getRoomId());
        $this->assertEquals(['besp'], $event->getLines());
    }

    public function testMergingHoursSeperateEvents() {
        $input = [
            '0U, 3762, wk9, wk9, "th",,, 0, 255,,,,, aBfn,,,, 3762, 410781708',
            'UN3 3',
            'Uf,, wd32',
            'Uk,, m31,, m32',
            'Ul,, rui,, rsn',
            'Ur,, R1_3.96,, R1_3.97',
            'Uz1 1 1 1 1 n',
            'Uz2 2 2 1 2 n',
            'UZ00, 4/5,, R1_3.96, R1_3.97',
            'UZ00, 4/6,, R1_3.96, R1_3.97',
            'UZ00, 4/7,, R1_3.96, R1_3.97'
        ];
        $lesson = new Lesson($this->schedule, implode("\n", $input));

        $events = $lesson->getEvents();
        $this->assertCount(2, $events);

        $eventA = $events[0];
        $this->assertEquals($eventA->getStartDateTime(),
            new \DateTime('2012-06-28T12:25:00'));
        $this->assertEquals($eventA->getEndDateTime(),
            new \DateTime('2012-06-28T14:55:00'));
        $this->assertEquals($eventA->getTeacherIds(), ['rui']);
        $this->assertEquals($eventA->getSchoolClassIds(), ['m31']);
        $this->assertEquals($eventA->getRoomId(), 'R1_3.96');

        $eventB = $events[1];
        $this->assertEquals($eventB->getStartDateTime(),
            new \DateTime('2012-06-28T12:25:00'));
        $this->assertEquals($eventB->getEndDateTime(),
            new \DateTime('2012-06-28T14:55:00'));
        $this->assertEquals($eventB->getTeacherIds(), ['rsn']);
        $this->assertEquals($eventB->getSchoolClassIds(), ['m32']);
        $this->assertEquals($eventB->getRoomId(), 'R1_3.97');
    }

    public function testLessonSorting() {

    }

}
