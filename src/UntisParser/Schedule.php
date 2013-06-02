<?php

namespace UntisParser;

class Schedule {

    /**
     * @var Teacher[]
     */
    private $teachers;

    /**
     * @var Subject[]
     */
    private $subjects;

    /**
     * @var SchoolClass[]
     */
    private $schoolClasses;

    /**
     * @var Room[]
     */
    private $rooms;

    /**
     * @var Lesson[]
     */
    private $lessons;

    /**
     * @var Header
     */
    private $header;

    /**
     * @var LessonGroup[]
     */
    private $lessonGroups;

    /**
     * @var Holiday[]
     */
    private $holidays;

    public function __construct($input) {
        $input = utf8_encode($input);
        $headers = [
            '00M', '00F', '00H', '00G', '00K', '00L', '00R', '00S', '00=', '00#', '00T',
            '00W', '00E', 'FE', '0U', '0S', '0T', '0H', '0D', '0s', 'RM'];
        $headers = array_flip(array_reverse($headers));
        foreach($headers as $header => &$data) {
            $objectStart = strpos($input, "\n" . $header);
            if($objectStart === false) {
                continue;
            }
            $data = substr($input, $objectStart);
            $input = substr($input, 0, $objectStart);
        }

        $this->header = new Header($input);

        // subjects
        $this->subjects = $this->getObjectsFromString($headers['00F'], '00F',
            'Subject');

        $this->teachers = $this->getObjectsFromString($headers['00L'], '00L',
            'Teacher', $this);
        Teacher::sortTeachers($this->teachers);

        $this->schoolClasses = $this->getObjectsFromString($headers['00K'], '00K',
            'SchoolClass');

        $this->rooms = $this->getObjectsFromString($headers['00R'], '00R', 'Room');

        $this->lessons = $this->getObjectsFromString($headers['0U'], '0U',
            'Lesson', $this);

        $this->lessonGroups = $this->getObjectsFromString($headers['00G'], '00G',
            'LessonGroup');

        $this->holidays = $this->getObjectsFromString($headers['FE'], 'FE',
            'Holiday');
        Holiday::sortHolidays($this->holidays);
    }

    private function getObjectsFromString($input, $header, $type,
                                          $schedule = null) {

        $output = [];
        $type = '\\UntisParser\\' . $type;
        while(!is_integer($input) && !empty($input)) {
            $objectStart = strrpos($input, "\n" . $header);
            if($objectStart === FALSE) {
                $objectStart = 0;
            }
            $objectString = substr($input, $objectStart);
            $objectString = trim($objectString);
            $input = substr($input, 0, $objectStart);
            if($schedule) {
                $output[] = new $type($this, $objectString);
            } else {
                $output[] = new $type($objectString);
            }
        }
        return $output;
    }

    /**
     * @return int
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * @return Teacher[int]
     */
    public function getTeachers() {
        return $this->teachers;
    }

    /**
     * @return Subject[]
     */
    public function getSubjects() {
        return $this->subjects;
    }

    /**
     * @return SchoolClass[]
     */
    public function getSchoolClasses() {
        return $this->schoolClasses;
    }

    /**
     * @return Room[]
     */
    public function getRooms() {
        return $this->rooms;
    }

    /**
     * @return Lesson[]
     */
    public function getLessons() {
        return $this->lessons;
    }

    /**
     * @return LessonGroup[]
     */
    public function getLessonGroups() {
        return $this->lessonGroups;
    }

    /**
     * @return Holiday[]
     */
    public function getHolidays() {
        return $this->holidays;
    }

    /**
     *
     * @param \DateTime $date
     * @return string
     */
    public function getWeekType(\DateTime $date) {
        $schoolyearStart = $this->header->getSchoolYearStart();
        $daysDifference = $schoolyearStart->diff($date)->days;
        $weekOffset = 0;

        // Make up for weeks not starting on Monday.
        $schoolyearStartWeekday = (int)$schoolyearStart->format('N');
        $daysDifference += $schoolyearStartWeekday - 1;

        // Check for holidays that've reset the counter.
        foreach($this->holidays as $holiday) {
            /* @var $diff \DateInterval */
            $diff = $holiday->getEndDate()->diff($date);
            $diff = ($diff->invert ? -1 : 1) * $diff->days;
            if($diff > 0 && $holiday->getWeekTypeAfter() !== null) {
                $daysDifference = $holiday->getEndDate()->diff($date)->d;
                $weekOffset = 0;
                $weekOffset = $holiday->getWeekTypeAfter();
            }
        }

        while($daysDifference > 6) {
            $weekOffset++;
            $daysDifference -= 7;
        }
        return $this->header->getFirstWeekId() + $weekOffset;
    }

    /**
     * @param string $id
     * @return LessonGroup
     */
    public function getLessonGroup($id) {

        foreach($this->lessonGroups as $lessongroup) {
            if($lessongroup->getId() === $id) {
                return $lessongroup;
            }
        }
        return null;
    }

    /**
     * @param int $dayNo
     * @param string $lessonGroupId
     * @return \DateTime[]
     */
    public function getDatesForDay($dayNo, $lessonGroupId) {
        $output = [];
        $dates = $this->header->getDatesForDay($dayNo);
        $lessonGroup = $this->getLessonGroup($lessonGroupId);
        $weektypes = $lessonGroup->getWeeks();

        foreach($dates as $date) {
            if(in_array($this->getWeekType($date), $weektypes)) {
                $output[] = $date;
            }
        }
        return $output;
    }

    /**
     * @param string $schoolClassId
     * @return LessonEvent
     */
    public function getLessonsForClass($schoolClassId) {
        $events = [];
        foreach($this->getLessons() as $lesson) {

            if(in_array($schoolClassId, $lesson->getSchoolClassIds())) {
                $lessonEvents = $lesson->getEvents();
                foreach($lessonEvents as $event) {
                    if(in_array($schoolClassId, $event->getSchoolClassIds())) {
                        $events[] = $event;
                    }
                }
            }
        }

        return $events;
    }

    /**
     * @param string $schoolClassId
     * @return LessonEvent
     */
    public function getLessonsForTeacher($teacherId) {
        $events = [];
        foreach($this->getLessons() as $lesson) {

            if(in_array($teacherId, $lesson->getTeacherIds())) {
                $lessonEvents = $lesson->getEvents();
                foreach($lessonEvents as $event) {
                    if(in_array($teacherId, $event->getTeacherIds())) {
                        $events[] = $event;
                    }
                }
            }
        }

        usort($events, '\UntisParser\LessonEvent::compare');
        return $events;
    }

    public function getSubject($subjectId) {
        foreach($this->subjects as $subject) {
            if($subject->getId() === $subjectId) {
                return $subject;
            }
        }
    }

    public function getTeacher($teacherId) {
        foreach($this->teachers as $teacher) {
            if($teacher->getId() === $teacherId) {
                return $teacher;
            }
        }
    }

}

