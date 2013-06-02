<?php

namespace UntisParser;

abstract class UntisObjectTest extends \PHPUnit_Framework_TestCase {

    public function doTestFile($filename, $header, $className, $schedule = null) {
        $input = file_get_contents($filename);
        $className = '\\UntisParser\\' . $className;
        while(!empty($input)) {
            $objectStart = strrpos($input, "\n" . $header);
            if($objectStart === FALSE) {
                $objectStart = 0;
            }
            $object = substr($input, $objectStart);
            $object = trim($object);
            $input = substr($input, 0, $objectStart);
            if($schedule) {
                new $className($schedule, $object);
            } else {
                new $className($object);
            }
        }
    }

}

?>
