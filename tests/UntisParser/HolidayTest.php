<?php

namespace UntisParser;

use UntisParser\Holiday;

class HolidayTest extends \PHPUnit_Framework_TestCase {

    public function testHoliday() {
        $input = 'FE0  ,meivakantie,MEIVAKANTIE,20120428,20120506,,0,0';
        $holiday = new Holiday($input);
        $this->assertSame('meivakantie', $holiday->getId());
        $this->assertSame('MEIVAKANTIE', $holiday->getName());
        $this->assertEquals(new \DateTime('2012-04-28'), $holiday->getStartDate());
        $this->assertEquals(new \DateTime('2012-05-06'), $holiday->getEndDate());
    }

    public function testHolidayWeekTypeAfter() {
        $input = 'FE2  ,meivakantie,MEIVAKANTIE,20120428,20120506,,0,0';
        $holiday = new Holiday($input);
        $this->assertSame(1, $holiday->getWeekTypeAfter());
    }

    public function testHolidaySorting() {
        $inputs = [
            'FE3  ,meivakantie,MEIVAKANTIE,20120628,20120707,,0,0',
            'FE0  ,hemelvaart,HEMELVAART,20120517,20120520,,0,0',
            'FE0  ,www,WWWW,20120628,20120607,F,0,0'
        ];

        /** @var $holidays Holiday[] */
        $holidays = [];
        foreach($inputs as $input) {
            $holidays[] = new Holiday($input);
        }

        $holidays = Holiday::sortHolidays($holidays);
        $this->assertSame('hemelvaart', $holidays[0]->getId());
        $this->assertSame('meivakantie', $holidays[1]->getId());
        $this->assertSame('www', $holidays[2]->getId());
    }

}
