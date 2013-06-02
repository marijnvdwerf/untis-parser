<?php

namespace UntisParser;

class LessonGroup {

    private $id;
    private $description;
    private $startDate;
    private $endDate;
    private $appliesToWeeks;

    public function __construct($input) {
        $data = explode("\n", $input);

        foreach($data as $line) {
            if(substr($line, 0, 3) === '00G') {
                $line = substr($line, 7);
                $lineData = str_getcsv($line);
                $this->id = $lineData[0];
                $this->description = $lineData[1];
            } else if(substr($line, 0, 2) === 'GR') {
                $line = substr($line, 4);
                $lineData = str_getcsv($line);
                if($lineData[0] !== '') {
                    $this->startDate = \DateTime::createFromFormat('Ymd', $lineData[0]);
                }
                if($lineData[1] !== '') {
                    $this->endDate = \DateTime::createFromFormat('Ymd', $lineData[1]);
                }
                $this->appliesToWeeks = $this->parseWeekPattern($lineData[2]);
            } else {
                throw new UnhandledLineException(substr($line, 0, 2) . "\n\n" . $input);
            }
        }
    }

    private function parseWeekPattern($input) {
        $output = [];
        $weeks = str_split($input);
        foreach($weeks as $weekNo => &$week) {
            if($week === 'X') {
                $output[] = $weekNo;
            }
        }
        return $output;
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
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * @return int[]
     */
    public function getWeeks() {
        return $this->appliesToWeeks;
    }

}

