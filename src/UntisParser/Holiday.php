<?php

namespace UntisParser;

class Holiday {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var int
     */
    private $weekTypeAfter;

    public function __construct($input) {
        $weekTypeAfter = (int)substr($input, 2, 2);
        if($weekTypeAfter > 0) {
            $this->weekTypeAfter = --$weekTypeAfter;
        }
        $input = str_getcsv(substr($input, 6));
        $this->id = $input[0];
        $this->name = $input[1];
        $this->startDate = $this->getDate($input[2]);
        $this->endDate = $this->getDate($input[3]);
    }

    /**
     * @param string $date
     * @return \DateTime
     */
    private function getDate($date) {
        $date = preg_replace('/^(\d{4})(\d{2})(\d{2})$/', '$1-$2-$3', $date);
        return new \DateTime($date);
    }

    /**
     * @param Holiday[] $holidays
     * @return \UntisParser\Holiday[]
     */
    public static function sortHolidays($holidays) {
        usort($holidays, function ($holidayA, $holidayB) {
            $diff = $holidayA->getStartDate()->diff($holidayB->getStartDate());
            $diff = ($diff->invert ? -1 : 1) * $diff->days;
            if($diff !== 0) {
                return -$diff;
            }
            return strcasecmp($holidayA->getId(), $holidayB->getId());
        });
        return $holidays;
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
     * @return int
     */
    public function getWeekTypeAfter() {
        return $this->weekTypeAfter;
    }

}
