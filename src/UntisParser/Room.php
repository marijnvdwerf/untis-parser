<?php

namespace UntisParser;

class Room {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var UntisParser/Color
     */
    private $backgroundColour;

    /**
     * @var UntisParser/Color
     */
    private $foregroundColour;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $departmentId;

    /**
     * @var int
     */
    private $capacity;

    /**
     * @var int[]
     */
    private $hourlyStatuses;

    public function __construct($input) {
        $data = explode("\n", $input);

        foreach($data as $line) {
            if(substr($line, 0, 3) === '00R') {
                $lineData = str_getcsv(substr($line, 7));
                $this->id = $lineData[0];
                $this->description = $lineData[1];
                $this->text = $lineData[3];

                $this->foregroundColour = new Color($lineData[6]);
                $this->backgroundColour = new Color($lineData[7]);
            } else if(substr($line, 0, 2) === 'SP') {
                $this->departmentId = substr($line, 7);
            } else if(substr($line, 0, 2) === 'RA') {
                $this->capacity = (int)substr($line, 2);
            } else if(substr($line, 0, 2) === 'ZA') {
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
     * @return UntisParser/Color
     */
    public function getBackgroundColour() {
        return $this->backgroundColour;
    }

    /**
     * @return UntisParser/Color
     */
    public function getForegroundColour() {
        return $this->foregroundColour;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getDepartmentId() {
        return $this->departmentId;
    }

    /**
     * @return int
     */
    public function getCapacity() {
        return $this->capacity;
    }

}

