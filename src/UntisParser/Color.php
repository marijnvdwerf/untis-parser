<?php

namespace UntisParser;

class Color {

    /**
     * @var int
     */
    protected $red;

    /**
     * @var int
     */
    protected $green;

    /**
     * @var int
     */
    protected $blue;

    /**
     * @param string $color
     */
    public function __construct($color) {
        if(empty($color)) {
            return;
        }
        $color = (int)$color;

        $this->blue = ($color >> 16) & 0xFF;
        $this->green = ($color >> 8) & 0xFF;
        $this->red = ($color & 0xFF);
    }

    /**
     * @return int
     */
    public function getRed() {
        return $this->red;
    }

    /**
     * @return int
     */
    public function getGreen() {
        return $this->green;
    }

    /**
     * @return int
     */
    public function getBlue() {
        return $this->blue;
    }

    /**
     * @return string
     */
    public function getHex() {
        if($this->red === null) {
            return null;
        }
        $hex = '#';
        $hex .= str_pad(dechex($this->red), 2, '0', STR_PAD_LEFT);
        $hex .= str_pad(dechex($this->green), 2, '0', STR_PAD_LEFT);
        $hex .= str_pad(dechex($this->blue), 2, '0', STR_PAD_LEFT);
        return $hex;
    }

}

