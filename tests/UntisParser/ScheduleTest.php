<?php

namespace UntisParser;

use UntisParser\Schedule;

class ScheduleTest extends \PHPUnit_Framework_TestCase {

    public function testObjectCount() {
//      $schedule = new Schedule(file_get_contents('Data/fhict11-12 kw4.gpn'));
//
//      $this->assertCount(128, $schedule->getSchoolClasses());
//      $this->assertCount(168, $schedule->getTeachers());
//      $this->assertCount(174, $schedule->getRooms());
//      $this->assertCount(1259, $schedule->getSubjects());
//      $this->assertCount(1720, $schedule->getLessons());
//      $this->assertCount(447, $schedule->getLessonGroups());
    }

    public function testWeekNumbers() {
        $input = 'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1';
        $schedule = new Schedule($input);

        $this->assertSame(0, $schedule->getWeekType(new \DateTime('2012-04-23')));
        $this->assertSame(0, $schedule->getWeekType(new \DateTime('2012-04-29')));
        $this->assertSame(1, $schedule->getWeekType(new \DateTime('2012-04-30')));
        $this->assertSame(2, $schedule->getWeekType(new \DateTime('2012-05-07')));
        $this->assertSame(3, $schedule->getWeekType(new \DateTime('2012-05-14')));
        $this->assertSame(4, $schedule->getWeekType(new \DateTime('2012-05-21')));
        $this->assertSame(5, $schedule->getWeekType(new \DateTime('2012-05-28')));
        $this->assertSame(6, $schedule->getWeekType(new \DateTime('2012-06-04')));
    }

    public function testWeekNumbersWhenStartsFriday() {
        $input = 'AA02 20120420 20120715 125 14 9  0  11 0  0   0     1';
        $schedule = new Schedule($input);

        $this->assertSame(0, $schedule->getWeekType(new \DateTime('2012-04-20')));
        $this->assertSame(0, $schedule->getWeekType(new \DateTime('2012-04-22')));
        $this->assertSame(1, $schedule->getWeekType(new \DateTime('2012-04-23')));
    }

    public function testWeekNumbersWithOffsetStart() {
        $input = 'AA02 20120423 20120715 125 14 9  0  21 0  0   0     1';
        $schedule = new Schedule($input);

        $this->assertSame(1, $schedule->getWeekType(new \DateTime('2012-04-23')));
        $this->assertSame(2, $schedule->getWeekType(new \DateTime('2012-04-30')));
    }

    public function testWeekNumbersWithHolidays() {
        $input = [
            'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1',
            'FE2  ,meivakantie,MEIVAKANTIE,20120428,20120507,,0,0'
        ];
        $schedule = new Schedule(implode("\n", $input));

        $this->assertSame(0, $schedule->getWeekType(new \DateTime('2012-04-23')));
        $this->assertSame(1, $schedule->getWeekType(new \DateTime('2012-05-08')));
        $this->assertSame(2, $schedule->getWeekType(new \DateTime('2012-05-14')));
    }

    public function testTeacherStatus() {
        $input = [
            'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1',
            'AA11 1 5  6 14 0 0           *10*11',
            'AA12 1 5  6 14 0 0           *10*11',
            'AA13 1 5  6 14 0 0           *10*11',
            'AA14 1 5  6 14 0 0           *10*11',
            'AA15 1 5  6 14 0 0           *10*11',
            'AA58      ,845~935,935~1025,1045~1135,1135~1225,1225~1315,1315~1405,1405~1455,1515~1605,1605~1655,1655~1745,1800~1850,1850~1940,2000~2050,2050~2140',
            '00L   ,ejv,"Erp, Jacqueline v.",,,,,,,,,,,,0,407181601',
            'ZT000000000000 ,,,, 2,  2',
            'Le ,,,,1,,,,M,,0,0,,,J.vanErp@fontys.nl'
        ];
        $input = implode("\n", $input);

        $thursdayMorning = new \DateTime('2012-04-26 10:00');
        $thursdayAfternoon = new \DateTime('2012-04-26 15:00');
        $fridayAfternoon = new \DateTime('2012-04-27 15:00');
        $this->markTestIncomplete('Determine how this API should work');
    }

//    public function testDaysForLessonGroup() {
//      $input = 'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1';
//      $schedule = new Schedule($input);
//
//      $this->assertSame([], $schedule->getWeekType(new \DateTime('2012-04-23')));
//    }


    public function testGetDaysForPeriod() {
        $input = [
            'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1',
            '00G   ,wk12358,"week 1+2+3+5+8",,,,,,,,,,,,0,409201830',
            'GR ,,,XXXX X  X'
        ];
        $schedule = new Schedule(implode("\n", $input));

        $this->assertSame(0, $schedule->getWeekType(new \DateTime('2012-04-29')));
        $days = $schedule->getDatesForDay(6, 'wk12358');

        $goalDays = [
            new \DateTime('2012-04-29'),
            new \DateTime('2012-05-06'),
            new \DateTime('2012-05-13'),
            new \DateTime('2012-05-20'),
            new \DateTime('2012-06-03'),
            new \DateTime('2012-06-24')
        ];
        $this->assertEquals($goalDays, $days);
    }

    public function testGetLessonsForClass() {
        $input = [
            'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1',
            'AA58      ,845~935,935~1025,1045~1135,1135~1225,1225~1315,1315~1405,1405~1455,1515~1605,1605~1655,1655~1745,1800~1850,1850~1940,2000~2050,2050~2140',
            '00G   ,wk1-10,"week 1t/m10",,,,,,,,,,,,0,409961720',
            'GR ,,,XXXXXXXXXXX',
            '00K   ,m43,,,"M",,,0,16579766,,,,,,0,409971439',
            'SP    ,P8',
            'ZT000000000000',
            'KL             0                       0',
            '0U ,473,wk1-10,wk1-10,"besp",,,,,,,,,aBn,,,,473,410161707',
            'UN2                  2',
            'Uf ,,delta',
            'Uk ,,m41,,m42,,m43',
            'Ul ,,slas,,rui',
            'Ur ,,R1_4.103',
            'Uz1 1 3 1 1            ,,,,,9702',
            'Uz2 1 3 1 1            ,,,,,9703',
            'UZ10 ,4/1,,R1_4.103',
            'UZ10 ,4/2,,R1_4.103'
        ];
        $schedule = new Schedule(implode("\n", $input));

        $lessons = $schedule->getLessonsForClass('m43');
        $this->markTestIncomplete();
    }

}
