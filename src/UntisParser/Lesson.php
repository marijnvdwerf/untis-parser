<?php

namespace UntisParser;

class Lesson {

    /**
     * @var Schedule
     */
    private $schedule;
    private $id;
    private $text;
    private $subjects = [];
    private $schoolClasses = [];
    private $teachers = [];
    private $rooms = [];

    /**
     * @var LessonMoment[]
     */
    private $moments = [];
    private $foregroundColour;
    private $backgroundColour;
    private $lessonGroupId;

    /**
     * @var LessonSlot[]
     */
    private $slots = [];

    /**
     *
     * @param Schedule $schedule
     * @param string $input
     */
    public function __construct($schedule, $input) {
        $this->schedule = $schedule;
        $data = explode("\n", $input);

        foreach($data as $line) {
            $code = substr($line, 0, 2);
            $line = trim($line);
            switch($code) {
                case '0U':
                    $data = str_getcsv(str_replace('\\', '\\\\', substr($line, 4)));
                    $this->id = (int)$data[0];
                    $this->lessonGroupId = trim($data[1]);
                    $this->text = $data[3];
                    $this->foregroundColour = new Color($data[6]);
                    $this->backgroundColour = new Color($data[7]);
                    break;
                case 'Uf':
                    $this->subjects = explode(',,', substr($line, 5));
                    break;
                case 'Uk':
                    $this->schoolClasses = explode(',,', substr($line, 5));
                    foreach($this->schoolClasses as &$schoolClass) {
                        $schoolClass = trim($schoolClass);
                    }
                    break;
                case 'Ul':
                    $this->teachers = explode(',,', substr($line, 5));
                    foreach($this->teachers as &$teacher) {
                        $teacher = trim($teacher);
                    }
                    break;
                case 'Ur':
                    $this->rooms = explode(',,', substr($line, 5));
                    foreach($this->rooms as &$room) {
                        $room = trim($room);
                    }
                    break;
                case 'Uz':
                    $newMoment = new LessonMoment($this, $line);
                    foreach($this->moments as $existingMoment) {
                        if($existingMoment->getRoomId() === $newMoment->getRoomId() &&
                            $existingMoment->getSubjectId() === $newMoment->getSubjectId() &&
                            $existingMoment->getLines() === $newMoment->getLines()
                        ) {
                            if(!is_array($existingMoment->getTeacherIds())) {
                                var_dump($input);
                            }
                            $existingMoment->merge($newMoment);

                            break 2;
                        }
                    }
                    $this->moments[] = $newMoment;
                    break;
                case 'UZ':
                    $slots = LessonSlot::parse($line);
                    foreach($slots as $newSlot) {

                        foreach($this->slots as $existingSlot) {
                            if($newSlot->getRoomId() === $existingSlot->getRoomId() &&
                                $newSlot->getDay() === $existingSlot->getDay()
                            ) {
                                if($this->schedule->getHeader()->areHoursFlush($existingSlot->getEndHour(),
                                    $newSlot->getStartHour()) ||
                                    $this->schedule->getHeader()->areHoursFlush($newSlot->getEndHour(),
                                        $existingSlot->getStartHour())
                                ) {
                                    $existingSlot->merge($newSlot);
                                    continue 2;
                                }
                            }
                        }

                        $this->slots[] = $newSlot;
                    }
                default:
                    //throw new UnhandledLineException;
            }
        }
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @return Color
     */
    public function getForegroundColour() {
        return $this->foregroundColour;
    }

    /**
     * @return Color
     */
    public function getBackgroundColour() {
        return $this->backgroundColour;
    }

    /**
     * @return String[]
     */
    public function getSchoolClassIds() {
        return $this->schoolClasses;
    }

    /**
     * @return String[]
     */
    public function getSubjectIds() {
        return $this->subjects;
    }

    /**
     * @return String[]
     */
    public function getTeacherIds() {
        return $this->teachers;
    }

    /**
     * @return String[]
     */
    public function getRoomIds() {
        return $this->rooms;
    }

    /**
     * @return LessonMoment[]
     */
    public function getMoments() {
        return $this->moments;
    }

    /**
     * @param string $roomId
     * @return LessonMoment[]
     */
    public function getMomentsForRoom($roomId) {
        $out = [];
        foreach($this->getMoments() as $moment) {
            if($moment->getRoomId() === $roomId) {
                $out[] = $moment;
            }
        }
        return $out;
    }

    /**
     *
     * @return LessonSlot[]
     */
    public function getSlots() {
        return $this->slots;
    }

    /**
     *
     * @return LessonEvent[]
     */
    public function getEvents() {
        $events = [];

        foreach($this->getSlots() as $slot) {
            $dates = $this->schedule->getDatesForDay($slot->getDay(),
                $this->lessonGroupId);

            /* @var $startTime */
            $startTime = $this->schedule->getHeader()->getHourStartTime($slot->getStartHour());
            $startTime = explode(':', $startTime);


            $endTime = $this->schedule->getHeader()->getHourEndTime($slot->getEndHour());
            $endTime = explode(':', $endTime);

            foreach($dates as $date) {
                $startDateTime = clone $date;
                $startDateTime->setTime((int)$startTime[0], (int)$startTime[1], 0);

                $endDateTime = clone $date;
                $endDateTime->setTime((int)$endTime[0], (int)$endTime[1], 0);

                foreach($this->getMomentsForRoom($slot->getRoomId()) as $moment) {
                    $lines = [];
                    if(!empty($this->text)) {
                        $lines[] = $this->text;
                    }
                    if($moment->getLines()) {
                        $lines = array_merge($lines, $moment->getLines());
                    }
                    $event = new LessonEvent($startDateTime, $endDateTime,
                        $moment->getTeacherIds(),
                        $moment->getRoomId(),
                        $moment->getSubjectId(),
                        $moment->getSchoolClassIds(), $lines);

                    $events[] = $event;
                }
            }
        }
        return $events;
    }

}

class LessonEvent {

    /**
     * @var \DateTime
     */
    private $startDateTime;

    /**
     * @var \DateTime
     */
    private $endDateTime;

    /**
     * @var String[]
     */
    private $teacherIds = [];

    /**
     * @var String
     */
    private $roomId;

    /**
     * @var String
     */
    private $subjectId;

    /**
     * @var String[]
     */
    private $schoolClassIds = [];

    /**
     * @var String[]
     */
    private $lines = [];

    /**
     *
     * @param \DateTime $startDateTime
     * @param \DateTime $endDateTime
     * @param String[] $teacherIds
     * @param String $roomId
     * @param String $subjectId
     * @param String[] $schoolClassIds
     * @param String[] $lines
     */
    public function __construct($startDateTime, $endDateTime, $teacherIds,
                                $roomId, $subjectId, $schoolClassIds, $lines) {
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->teacherIds = $teacherIds;
        $this->roomId = $roomId;
        $this->subjectId = $subjectId;
        $this->schoolClassIds = $schoolClassIds;
        $this->lines = $lines;
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime() {
        return $this->startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTime() {
        return $this->endDateTime;
    }

    /**
     * @return string[]
     */
    public function getTeacherIds() {
        return $this->teacherIds;
    }

    /**
     * @return string
     */
    public function getRoomId() {
        return $this->roomId;
    }

    /**
     * @return string
     */
    public function getSubjectId() {
        return $this->subjectId;
    }

    /**
     * @return string[]
     */
    public function getSchoolClassIds() {
        return $this->schoolClassIds;
    }

    public function getLines() {
        return $this->lines;
    }

    /**
     *
     * @param LessonEvent $eventA
     * @param LessonEvent $eventB
     */
    public static function compare($eventA, $eventB) {
        $diff = $eventA->getStartDateTime()->format('U') - $eventB->getStartDateTime()->format('U');
        if($diff !== 0) {
            return $diff;
        }

        $diff = $eventA->getEndDateTime()->format('U') - $eventB->getEndDateTime()->format('U');
        if($diff !== 0) {
            return $diff;
        }

        return 0;
    }

}

class LessonMoment {

    /**
     * @var string[]
     */
    private $teacherIds = [];

    /**
     * @var string
     */
    private $subjectId;

    /**
     * @var string
     */
    private $roomId;

    /**
     * @var string[]
     */
    private $schoolClassIds = [];

    /**
     * @var string[]
     */
    private $lines = [];

    /**
     *
     * @param Lesson $parentLesson
     * @param string $input
     */
    public function __construct($parentLesson, $input) {
        $data = str_getcsv($input);
        $line = $data[0];
        $line = substr($line, 2);

        $teacherIndex = (int)substr($line, 0, 2);
        $line = substr($line, 2);
        if($teacherIndex !== 0) {
            $this->teacherIds = [$parentLesson->getTeacherIds()[$teacherIndex - 1]];
        }

        if(count($parentLesson->getSchoolClassIds()) > 0) {
            $schoolClassIndexFrom = (int)substr($line, 0, 2) - 1;
            $line = substr($line, 2);
            $schoolClassIndexTo = (int)substr($line, 0, 2);
            $line = substr($line, 2);
            if($schoolClassIndexFrom > -1) {
                $this->schoolClassIds = array_slice($parentLesson->getSchoolClassIds(),
                    $schoolClassIndexFrom,
                    $schoolClassIndexTo - $schoolClassIndexFrom);
            }
        }

        $subjectIndex = (int)substr($line, 0, 2) - 1;
        $line = substr($line, 2);
        if($subjectIndex > -1) {
            $this->subjectId = $parentLesson->getSubjectIds()[$subjectIndex];
        }


        $roomIndex = (int)substr($line, 0, 2) - 1;
        $line = substr($line, 2);
        if($roomIndex > -1) {
            $this->roomId = $parentLesson->getRoomIds()[$roomIndex];
        }

        if(isset($data[1]) && !empty($data[1])) {
            $this->lines[] = $data[1];
        }
    }

    /**
     * @param LessonMoment $moment
     */
    public function merge($moment) {
        if(!is_array($this->teacherIds)) {
            var_dump($this);
        }
        $this->teacherIds = array_merge($this->teacherIds, $moment->teacherIds);
        $this->teacherIds = array_unique($this->teacherIds);

        $this->schoolClassIds = array_merge($this->schoolClassIds,
            $moment->schoolClassIds);
        $this->schoolClassIds = array_unique($this->schoolClassIds);
    }

    /**
     * @return string[]
     */
    public function getTeacherIds() {
        return $this->teacherIds;
    }

    /**
     * @return string
     */
    public function getSubjectId() {
        return $this->subjectId;
    }

    /**
     * @return string
     */
    public function getRoomId() {
        return $this->roomId;
    }

    /**
     * @return string[]
     */
    public function getSchoolClassIds() {
        return $this->schoolClassIds;
    }

    /**
     * @return string[]
     */
    public function getLines() {
        return $this->lines;
    }

}

class LessonSlot {

    /**
     * @var int
     */
    private $day;

    /**
     * @var int
     */
    private $startHour;

    /**
     * @var int
     */
    private $endHour;

    /**
     * @var string
     */
    private $roomId;

    /**
     *
     * @param String $line
     * @return \UntisParser\LessonSlot[]
     */
    public static function parse($line) {
        $output = [];
        $data = str_getcsv(substr($line, 6));

        $time = explode('/', $data[0]);
        $day = (int)$time[0] - 1;
        $hour = (int)$time[1] - 1;
        $rooms = array_slice($data, 2);

        foreach($rooms as $room) {
            $slot = new LessonSlot();
            $slot->day = $day;
            $slot->startHour = $slot->endHour = $hour;
            $slot->roomId = trim($room);
            $output[] = $slot;
        }
        return $output;
    }

    /**
     * @param LessonSlot $slot
     */
    public function merge($slot) {
        $this->startHour = min([$this->startHour, $slot->startHour]);
        $this->endHour = max([$this->endHour, $slot->endHour]);
    }

    /**
     * @return int
     */
    public function getDay() {
        return $this->day;
    }

    /**
     * @return int
     */
    public function getStartHour() {
        return $this->startHour;
    }

    /**
     * @return int
     */
    public function getEndHour() {
        return $this->endHour;
    }

    /**
     * @return string
     */
    public function getRoomId() {
        return $this->roomId;
    }

}