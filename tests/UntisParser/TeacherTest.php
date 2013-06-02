<?php

namespace UntisParser;

use UntisParser\Teacher;

class TeacherTest extends UntisObjectTest {

    /**
     * @var Schedule
     */
    protected $schedule;


    public function setUp() {
        $this->schedule = new Schedule(
            implode("\n", [
                'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1',
                'AA11 1 6  7 14 0 0           *10*11',
                'AA12 1111 1213 0 0           *10*11',
                'AA13 1 5  6 14 0 0           *10*11',
                'AA14 1 5  6 14 0 0           *10*11',
                'AA15 1 5  6 14 0 0           *10*11'
            ])
        );
    }

    public function testAllDayStatus() {
        $input = [
            '00L   ,tms,"Tillemans,Glenn",,,,,,,,,,,,0,410011148',
            'ZT000000000000 ,  1,  1,,  1',
            'Le ,,,,0;4,,,,M,,0,0,,,g.tillemans@fontys.nl'
        ];
        $teacher = new Teacher($this->schedule, implode("\n", $input));
        $this->assertSame($teacher->getId(), 'tms');
        $this->assertSame($teacher->getName(), 'Tillemans,Glenn');
        $this->assertSame($teacher->getEmail(), 'g.tillemans@fontys.nl');
        $this->assertSame($teacher->getStatusForHour(0, 0), -3);
        $this->assertSame($teacher->getStatusForHour(0, 13), -3);
    }

    public function testAfternoonStatus() {
        $input = [
            '00L   ,rui,"Ruissen, Martijn",,,,,,,,,,,,0,410011146',
            'ZT000000000000 ,,,, 1,  1',
            'Le ,,,,0.8,,,,M,,0,0,,,m.ruissen@fontys.nl'
        ];
        $teacher = new Teacher($this->schedule, implode("\n", $input));
        $this->assertSame($teacher->getStatusForHour(3, 4), 0);
        $this->assertSame($teacher->getStatusForHour(3, 5), -3);
        $this->assertSame($teacher->getStatusForHour(3, 13), -3);
    }

    public function testAllDayPlus3Status() {
        $input = [
            '00L   ,ven,"Ven, Antoine v.d.",,"lectoraat",,,,,,,,,,0,409671222',
            'ZT000000000000 ,,,  6',
            'Le ,,,,,,,,alg,,0,0,,,antoine.vandeven@fontys.nl'
        ];
        $teacher = new Teacher($this->schedule, implode("\n", $input));
        $this->assertSame($teacher->getStatusForHour(2, 0), +3);
        $this->assertSame($teacher->getStatusForHour(2, 13), +3);
    }

    public function testHourlyStatus() {
        $input = [
            '00L   ,rvs,"Reuvers,Nico",,,,,,,,,,,,0,398261123',
            'ZA ,666662222     111111111     111111111     111111111     111111111',
            'ZT000000000000'
        ];
        $teacher = new Teacher($this->schedule, implode("\n", $input));
        $this->assertSame($teacher->getStatusForHour(0, 4), +3);
        $this->assertSame($teacher->getStatusForHour(0, 5), -2);
        $this->assertSame($teacher->getStatusForHour(1, 0), -3);
    }

    public function testUnsetStatus() {
        $input = [
            '00L   ,tms,"Tillemans,Glenn",,,,,,,,,,,,0,410011148',
            'ZT000000000000 ,  1,  1,,  1',
            'Le ,,,,0;4,,,,M,,0,0,,,g.tillemans@fontys.nl'
        ];
        $teacher = new Teacher($this->schedule, implode("\n", $input));
        $this->assertSame(0, $teacher->getStatusForHour(2, 0));
    }


    public function testJacquelineVanErp() {
        $input = [
            '00L   ,ejv,"Erp, Jacqueline v.",,,,,,,,,,,,0,410781435',
            'ZT000000000000 ,,,,,  3',
            'Le ,,,,1,,,,M,,0,0,,,J.vanErp@fontys.nl'
        ];
        $teacher = new Teacher($this->schedule, implode("\n", $input));
        $this->assertSame(-1, $teacher->getStatusForHour(4, 0));
        $this->assertSame(-1, $teacher->getStatusForHour(4, 13));
    }

    public function testFontysTeachers() {
        $this->doTestFile(__BASE__ . '/tests/Data/Teachers.txt', '00L', 'Teacher', $this->schedule);
    }

    public function testSortTeachersById() {
        $inputs = [
            '00L   ,WHN,"Wijnhoven, Karinka",,"inhuur",,,,,,,N,,,0,410011152',
            '00L   ,zwt,"Zwartjes, Gerrie",,,,,,,,,,,,0,408541324',
            '00L   ,gms,"Graaumans, Joris",,,,,,,,,,,,0,409671130'
        ];

        $teachers = [];
        foreach($inputs as $input) {
            $teachers[] = new Teacher($this->schedule, $input);
        }

        Teacher::sortTeachers($teachers);

        $this->assertSame('gms', $teachers[0]->getId());
        $this->assertSame('WHN', $teachers[1]->getId());
        $this->assertSame('zwt', $teachers[2]->getId());
    }

}
