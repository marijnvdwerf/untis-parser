<?php

namespace UntisParser;

class Header {

    /**
     * @var \DateTime
     */
    private $schoolYearStart;

    /**
     * @var \DateTime
     */
    private $schoolYearEnd;

    /**
     * @var int
     */
    private $weekCount;

    /**
     * @var int
     */
    private $daysPerWeek;

    /**
     * @var int
     */
    private $hoursPerDay;

    /**
     * @var int
     */
    private $firstWeekId;

    /**
     * @var int
     */
    private $firstHourNo;

    /**
     * @var int
     */
    private $firstDayOfWeek;

    /**
     * @var HeaderHour[int]
     */
    private $hourTimes;

    /**
     * @var Day[int]
     */
    private $days = [];

    const DAY_MONDAY = 1;
    const DAY_TUESDAY = 2;
    const DAY_WEDNESDAY = 3;
    const DAY_THURSDAY = 4;
    const DAY_FRIDAY = 5;
    const DAY_SATURDAY = 6;
    const DAY_SUNDAY = 7;

    /**
     * @param string $input
     */
    public function __construct($input) {
        $data = explode("\n", $input);

        foreach($data as $line) {
            $code = substr($line, 0, 4);
            if($code === 'AA02') {
                $this->schoolYearStart = $this->getDate(substr($line, 5, 8));
                $this->schoolYearEnd = $this->getDate(substr($line, 14, 8));
                $this->weekCount = (int)substr($line, 23, 2);
                $this->daysPerWeek = (int)substr($line, 25, 1);
                $this->hoursPerDay = (int)substr($line, 27, 2);

                $this->firstWeekId = (int)substr($line, 36, 1) - 1;
                $this->firstHourNo = (int)substr($line, 37, 1);
                $this->firstDayOfWeek = (int)substr($line, 52, 1);
            } else if($code === 'AA58') {

                $schedule = str_getcsv(substr($line, 11));
                $this->hourTimes = [];
                foreach($schedule as $hour) {
                    $hour = explode('~', $hour);
                    $this->hourTimes[] = new HeaderHour($this->getTime($hour[0]),
                        $this->getTime($hour[1]));
                }
            } else if(substr($code, 0, 3) === 'AA1') {
                $dayNo = substr($code, 3, 1) - 1;
                $day = new Day(substr($line, 5));
                $this->days[$dayNo] = $day;
            }
        }
    }

    /**
     * @param string $date
     * @return \DateTime
     */
    private function getDate($date) {
        $date = preg_replace('/^(\d{4})(\d{2})(\d{2})$/', '$1-$2-$3', $date);
        return new \DateTime($date);
    }

    private function getTime($time) {
        return preg_replace_callback('/(\d{1,2})(\d{2})/',
            function ($match) {
                return str_pad($match[1], 2, '0', STR_PAD_LEFT) . ':' . $match[2];
            }, $time);
    }

    public function getHourStartTime($hour) {
        return $this->hourTimes[$hour]->startTime;
    }

    public function getHourEndTime($hour) {
        return $this->hourTimes[$hour]->endTime;
    }

    /**
     * @return \DateTime
     */
    public function getSchoolYearStart() {
        return clone $this->schoolYearStart;
    }

    /**
     * @return \DateTime
     */
    public function getSchoolYearEnd() {
        return $this->schoolYearEnd;
    }

    /**
     * @return int
     */
    public function getWeekCount() {
        return $this->weekCount;
    }

    /**
     * @return int
     */
    public function getDaysPerWeek() {
        return $this->daysPerWeek;
    }

    /**
     * @return int
     */
    public function getHoursPerDay() {
        return $this->hoursPerDay;
    }

    /**
     * @return string
     */
    public function getFirstWeekId() {
        return $this->firstWeekId;
    }

    /**
     * @return int
     */
    public function getFirstHourNo() {
        return $this->firstHourNo;
    }

    /**
     * @return int
     */
    public function getFirstDayOfWeek() {
        return $this->firstDayOfWeek;
    }

    public function getDaypart($day, $hour) {

        if(isset($this->days[$day])) {
            return $this->days[$day]->getDaypart($hour);
        }
        return null;
    }

    public function getDatesForDay($dayNo) {
        $dayNo = $this->getFirstDayOfWeek() + $dayNo;

        $dates = [];
        $schoolYearFirstDayNo = (int)$this->getSchoolYearStart()->format('N');
        $startOffset = $dayNo - $schoolYearFirstDayNo;
        if($startOffset < 0) {
            $startOffset += 7;
        }

        $date = $this->getSchoolYearStart()->add(new \DateInterval('P' . $startOffset . 'D'));

        while($date <= $this->getSchoolYearEnd()) {
            $dates[] = clone $date;
            $date->add(new \DateInterval('P7D'));
        }

        return $dates;
    }

    public function areHoursFlush($hourA, $hourB) {
        $firstHour = min([$hourA, $hourB]);
        $secondHour = max([$hourA, $hourB]);
        return $this->getHourEndTime($firstHour) === $this->getHourStartTime($secondHour);
    }

}

class HeaderHour {

    public $startTime;
    public $endTime;

    function __construct($startTime, $endTime) {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

}

class Day {

    const DAYPART_MORNING = 0;
    const DAYPART_AFTERNOON = 1;
    const DAYPART_ALL = 2;

    public $firstMorningHour;
    public $lastMorningHour;
    public $firstAfternoonHour;
    public $lastAfternoonHour;

    public function __construct($input) {
        $this->firstMorningHour = substr($input, 0, 2) - 1;
        $this->lastMorningHour = substr($input, 2, 2) - 1;

        $this->firstAfternoonHour = substr($input, 5, 2) - 1;
        $this->lastAfternoonHour = substr($input, 7, 2) - 1;
    }

    public function getDaypart($hour) {
        if($hour >= $this->firstMorningHour && $hour <= $this->lastMorningHour) {
            return Day::DAYPART_MORNING;
        } else if($hour >= $this->firstAfternoonHour && $hour <= $this->lastAfternoonHour) {
            return Day::DAYPART_AFTERNOON;
        }
        return null;
    }

}