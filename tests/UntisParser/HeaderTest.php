<?php

namespace UntisParser;

use UntisParser\Header;

class HeaderTest extends \PHPUnit_Framework_TestCase {

    public function testSchoolYear() {
        //                               AAB CC       FG              K
        $input = 'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1';
        $header = new Header($input);
        $this->assertEquals(new \DateTime('2012-04-23'),
            $header->getSchoolYearStart());
        $this->assertEquals(new \DateTime('2012-07-15'),
            $header->getSchoolYearEnd());
        $this->assertSame(12, $header->getWeekCount()); // A
        $this->assertSame(5, $header->getDaysPerWeek()); // B
        $this->assertSame(14, $header->getHoursPerDay()); // C


        $this->assertSame(0, $header->getFirstWeekId()); // F
        $this->assertSame(1, $header->getFirstHourNo()); // G
        $this->assertSame(Header::DAY_MONDAY, $header->getFirstDayOfWeek()); // K
    }

    public function testScheduleTimes() {
        //           D ===  ==== ===
        $input = 'AA58      ,845~935,935~1025,1045~1135,1135~1225,1225~1315,1315~1405,1405~1455,1515~1605,1605~1655,1655~1745,1800~1850,1850~1940,2000~2050,2050~2140';
        $header = new Header($input);
        $this->assertSame('13:15', $header->getHourStartTime(5));
        $this->assertSame('14:55', $header->getHourEndTime(6));
    }

    public function testDayParts() {
        $input = [
            'AA11 1 6  7 14 0 0           *10*11',
            'AA12 1111 1213 0 0           *10*11',
            'AA13 1 5  6 14 0 0           *10*11',
            'AA14 1 5  6 14 0 0           *10*11',
            'AA15 1 5  6 14 0 0           *10*11'
        ];
        $header = new Header(implode("\n", $input));
        $this->assertSame(Day::DAYPART_MORNING, $header->getDaypart(0, 0));
        $this->assertSame(Day::DAYPART_MORNING, $header->getDaypart(0, 5));
        $this->assertSame(Day::DAYPART_AFTERNOON,
            $header->getDaypart(0, 6));
        $this->assertSame(Day::DAYPART_AFTERNOON,
            $header->getDaypart(0, 13));
        $this->assertSame(null, $header->getDaypart(1, 0));
        $this->assertSame(null, $header->getDaypart(1, 9));
        $this->assertSame(Day::DAYPART_MORNING, $header->getDaypart(1, 10));
    }

    public function testGetDays() {
        $input = 'AA02 20120423 20120715 125 14 9  0  11 0  0   0     1';
        $header = new Header($input);
        $days = $header->getDatesForDay(6);


        $goalDays = [
            new \DateTime('2012-04-29'),
            new \DateTime('2012-05-06'),
            new \DateTime('2012-05-13'),
            new \DateTime('2012-05-20'),
            new \DateTime('2012-05-27'),
            new \DateTime('2012-06-03'),
            new \DateTime('2012-06-10'),
            new \DateTime('2012-06-17'),
            new \DateTime('2012-06-24'),
            new \DateTime('2012-07-01'),
            new \DateTime('2012-07-08'),
            new \DateTime('2012-07-15'),
        ];
        $this->assertEquals($goalDays, $days);
    }

    public function testLessonsAreFlush() {
        $input = 'AA58      ,845~935,935~1025,1045~1135,1135~1225,1225~1315,1315~1405,1405~1455,1515~1605,1605~1655,1655~1745,1800~1850,1850~1940,2000~2050,2050~2140';
        $header = new Header($input);

        $this->assertTrue($header->areHoursFlush(0, 1));

        // break
        $this->assertFalse($header->areHoursFlush(1, 2));

        $this->assertTrue($header->areHoursFlush(2, 3));
        $this->assertTrue($header->areHoursFlush(3, 4));
        $this->assertTrue($header->areHoursFlush(4, 5));
        $this->assertTrue($header->areHoursFlush(5, 6));

        // break
        $this->assertFalse($header->areHoursFlush(6, 7));

        $this->assertTrue($header->areHoursFlush(7, 8));
        $this->assertTrue($header->areHoursFlush(8, 9));
    }

    public function testLessonsAreFlushAnyOrderAllowed() {
        $input = 'AA58      ,845~935,935~1025,1045~1135,1135~1225,1225~1315,1315~1405,1405~1455,1515~1605,1605~1655,1655~1745,1800~1850,1850~1940,2000~2050,2050~2140';
        $header = new Header($input);

        $this->assertTrue($header->areHoursFlush(1, 0));

        // break
        $this->assertFalse($header->areHoursFlush(2, 1));

        $this->assertTrue($header->areHoursFlush(3, 2));
    }

}
