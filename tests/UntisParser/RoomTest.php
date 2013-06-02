<?php

namespace UntisParser;

use UntisParser\Room;

require_once 'UntisObjectTest.php';

class RoomTest extends UntisObjectTest {

    public function testBasicRoom() {
        $input = '00R   ,P1_B0.13,"30 tilburg",,,,,0,65535,,,N,,,0,408401405';
        $room = new Room($input);
        $this->assertEquals('P1_B0.13', $room->getId());
        $this->assertEquals('30 tilburg', $room->getDescription());
        $this->assertEquals('#ffff00', $room->getBackgroundColour()->getHex());
    }

    public function testRoomWithTextAndDepartment() {
        $input = [
            '00R   ,R1_3.89,"LAB mm / lifestyle",,"kitchen",,,,,,,,,,0,0',
            'SP    ,i'
        ];
        $room = new Room(implode("\n", $input));
        $this->assertEquals('kitchen', $room->getText());
        $this->assertEquals('i', $room->getDepartmentId());
    }

    public function testCapacity() {
        $input = [
            '00R   ,R1_1.242,"ap-pract.",,"computerlokaal",,,,,,,N,,,0,404721815',
            'SP    ,elec',
            'RA18'
        ];
        $room = new Room(implode("\n", $input));
        $this->assertEquals(18, $room->getCapacity());
    }

    public function testAvailability() {
        $input = [
            '00R   ,R1_3.40,"theorie 30 // 75",,"t/p",,,,,,,,,,0,408750947',
            'ZA ,1111111111111111111111111111',
            'SP    ,i'
        ];
        $room = new Room(implode("\n", $input));
        $this->assertSame(-3, $room->getStatusForHour(27));
        $this->assertSame(0, $room->getStatusForHour(28));
    }

    public function testFontysRooms() {
        $this->doTestFile(__BASE__ . '/tests/Data/Rooms.txt', '00R', 'Room');
    }

}
