<?php

namespace UntisParser;

class SchoolClass {

    private $id;
    private $text;
    private $foregroundColour;
    private $backgroundColour;
    private $hourlyStatuses;
    private $departmentId;

    public function __construct($input) {
        $data = explode("\n", $input);

        foreach($data as $line) {
            if(substr($line, 0, 3) === '00K') {
                $line = substr($line, 7);
                $lineData = str_getcsv($line);
                $this->id = $lineData[0];
                $this->text = $lineData[3];
                $this->foregroundColour = new Color($lineData[6]);
                $this->backgroundColour = new Color($lineData[7]);
            } else if(substr($line, 0, 2) === 'SP') {
                $this->departmentId = substr($line, 7);
            } else if(substr($line, 0, 2) === 'ZT') {
                // Unspecified day requests (requested)
            } else if(substr($line, 0, 2) === 'KL') {
                // ???
            } else if(substr($line, 0, 2) === 'ZA') {
                // Time requests
                $this->hourlyStatuses = $this->getStatusArray(substr($line, 4));
            } else {
                throw new UnhandledLineException(substr($line, 0, 2) . "\n\n" . $input);
            }
        }
    }

    private function getStatusArray($input) {
        $input = str_split($input);
        foreach($input as &$code) {
            if($code === ' ') {
                $code = 0;
            } else if($code <= 3) {
                $code = $code - 4;
            } else {
                $code = $code - 3;
            }
        }
        return $input;
    }

    public function getStatusForHour($hourIndex) {
        if(!isset($this->hourlyStatuses[$hourIndex])) {
            return 0;
        }
        return $this->hourlyStatuses[$hourIndex];
    }

    public function getId() {
        return $this->id;
    }

    public function getText() {
        return $this->text;
    }

    public function getForegroundColour() {
        return $this->foregroundColour;
    }

    public function getBackgroundColour() {
        return $this->backgroundColour;
    }

    public function getDepartmentId() {
        return $this->departmentId;
    }
}

