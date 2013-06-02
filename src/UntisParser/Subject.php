<?php

namespace UntisParser;

class Subject {

    private $id;
    // General
    private $marked;
    private $ignore;
    private $lock;
    private $dontPrint;
    private $text;
    private $description;
    private $statisticalCode;
    // Subject
    private $subjectGroup;

    public function __construct($line) {
        // Remove '00F   ,';
        $line = substr($line, 7);
        $data = str_getcsv($line);
        $this->id = $data[0];
        $this->description = $data[1];

        $flags = $data[10];
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

}

?>
