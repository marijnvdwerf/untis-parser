<?php

namespace UntisParser;

class Teacher {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $email;
    private $availability;
    private $hourlyStatuses;
    private $daypartStatuses;

    /**
     * @var Schedule
     */
    private $schedule;

    public function __construct($schedule, $input) {
        $this->schedule = $schedule;

        $data = explode("\n", $input);

        foreach($data as $line) {
            if(substr($line, 0, 3) === '00L') {
                $line = substr($line, 7);
                $line = str_getcsv($line);
                $this->id = $line[0];
                $this->name = $line[1];
            } else if(substr($line, 0, 2) === 'ZT') {
                // semi-daily status
                $undefined = substr($line, 2, 12);

                $planned = substr($line, 16);
                $planned = explode(',', $planned);
                $this->daypartStatuses = array();
                foreach($planned as $day) {
                    $this->daypartStatuses[] = $this->getStatusArray($day);
                }
            } else if(substr($line, 0, 2) === 'ZA') {
                $hourlyStatus = substr($line, 4);
                $this->hourlyStatuses = $this->getStatusArray($hourlyStatus);
                // hourly status
            } else if(substr($line, 0, 2) === 'Le') {
                $line = substr($line, 4);
                $line = str_getcsv($line);

                if(isset($line[13])) {
                    $this->email = $line[13];
                }
            } else if(substr($line, 0, 2) === 'LK') {
                // Undefined blockings
                $undefinedDays = (int)substr($line, 14, 1);
                $undefinedStartHour = (int)substr($line, 10, 2);
                $undefinedEndHour = (int)substr($line, 12, 2);
            } else {
                throw new UnhandledLineException(substr($line, 0, 2) . "\n\n" . $input);
            }
        }
    }

    private function getStatusArray($input) {
        $input = str_split($input);
        foreach($input as &$code) {
            if($code === ' ' || $code == 0) {
                $code = 0;
            } else if($code <= 3) {
                $code = $code - 4;
            } else {
                $code = $code - 3;
            }
        }
        return $input;
    }

    /**
     * @param Teacher[] $teachers
     * @return Teacher[]
     */
    public static function sortTeachers(&$teachers) {
        usort($teachers, function ($teacherA, $teacherB) {
            return strcasecmp($teacherA->getId(), $teacherB->getId());
        });
    }

    /**
     * @param int $dayIndex
     * @param int $daypart
     * @return int
     */
    protected function getStatusForDaypart($dayIndex, $daypart) {
        if(!isset($this->daypartStatuses[$dayIndex]) || !isset($this->daypartStatuses[$dayIndex][$daypart])) {
            return 0;
        }
        return $this->daypartStatuses[$dayIndex][$daypart];
    }

    /**
     * @param int $hourIndex
     * @return int
     */
    public function getStatusForHour($day, $hour) {
        $status = $this->getStatusForDaypart($day, Day::DAYPART_ALL);
        if($status !== 0) {
            return $status;
        }

        $daypart = $this->schedule->getHeader()->getDaypart($day, $hour);
        if(!is_null($daypart)) {
            $status = $this->getStatusForDaypart($day, $daypart);
        }
        if($status !== 0) {
            return $status;
        }

        $hourIndex = $day * $this->schedule->getHeader()->getHoursPerDay() + $hour;
        if(!isset($this->hourlyStatuses[$hourIndex])) {
            return 0;
        }
        return $this->hourlyStatuses[$hourIndex];
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

}

