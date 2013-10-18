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
    
    /**
     * Converts every instance of <FOO> to $ANSI->FOO
     * 
     * Allows you to write a string as <WHITE> instead of {$ANSI->WHITE}
     * 
     * @param string $text The text to clean up
     * 
     * return string
     */
    public function EZ($text) {
        // TODO: Support end tags
        
        // Get all the tags. This supports <FOO> and <FOO:1> and <FOO:1:2>
        preg_match_all('/<([\w:]+)>/', $text, $matches, PREG_SET_ORDER);
        
        // Go through all the matches and convert to ansi calls
        foreach($matches as $match) {
            // Break up the match into components with ':'
            $arguments = explode(":", $match[1]);
            // Get the method (first argument)
            $method = array_shift($arguments);
            // Make the adjustment
            $text = str_replace($match[0], call_user_func_array(array($this, $method), $arguments), $text);
        }
        
        return $text;
    }
}
