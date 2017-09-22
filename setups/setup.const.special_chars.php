<?php
/**
 * setup.const.special_chars.php
 *
 * Définition de constante PHP représentant les caractères spéciaux à l'exemple de PHP_EOL valant \n.
 *
 * @author    Nicolas DUPRE
 * @release   22/09/2017
 * @version   1.0.0
 * @package   Prepend
 */

/** End Of Line */
define('CR', "\r");       // U+000D :: 0x0D :: Cariage Return               = MAC
define('LF', "\n");       // U+000A :: 0x0A :: Line Feed                    = unix
define('CRLF', "\r\n");   //        ::      :: Cariage Return + Line Feed   = Windows

// HTML shortcut
define('BR', "<br />\n"); // HTML Break

// ASCII control characters
define('ESC', "\e");      // U+001B :: 0x1B :: Escape
define('HT', "\t");       // U+0009 :: 0x09 :: Horizontal Tab (Normal)
define('VT', "\v");       // U+000B :: 0x0B :: Vertical Tab
define('FF', "\f");		  // U+000C	:: 0x0C :: Form Feed
define('NEL', "\u0085");  // U+0085	:: 0x85 :: Next Line
define('LS', "\u2028");	  // U+2028	::      :: Line Separator
define('PS', "\u2029");	  // U+2029	::      :: Paragraph Separator
