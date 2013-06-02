<?php

namespace UntisParser;

class ColorTest extends \PHPUnit_Framework_TestCase {

    public function testRGB() {
        $color = new Color('16579766');
        $this->assertSame(182, $color->getRed());
        $this->assertSame(252, $color->getGreen());
        $this->assertSame(252, $color->getBlue());
    }

    public function testEmpty() {
        $color = new Color('');
        $this->assertSame(null, $color->getRed());
        $this->assertSame(null, $color->getHex());
    }

}
