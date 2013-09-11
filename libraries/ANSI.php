<?php

/**
 * The purpose of this class is to provide formatting options for php strings
 */

class ANSI {
    public function __get($name) { return $this->$name(); }

    public function CSI(    ) { return "{$this->ESC()}["; }
    public function CUB($i=1) { return "{$this->CSI()}{$i}D"; } // CUB – Cursor Back
    public function CHA($i=1) { return "{$this->CSI()}{$i}G"; } // CHA – Cursor Horizontal Absolute
    public function ESC(    ) { return "\x1B"; }
    public function EL ($i=0) { return "{$this->CSI}{$i}K"; } // EL - Erase in Line
    // SGR
    public function CYAN () { return "{$this->CSI}36m"; }
    public function GREEN() { return "{$this->CSI}32m"; }
    public function RED  () { return "{$this->CSI}31m"; }
    public function RESET() { return "{$this->CSI}0m";  }
    public function WHITE() { return "{$this->CSI}37m"; }
}
