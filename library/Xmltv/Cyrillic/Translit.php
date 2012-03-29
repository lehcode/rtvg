<?php
/**
 * Class for transliterating Cyrillic to Latinic
 *
 * Examples of using class:
 *
 * 1. Creating class instance with arguments (argument is the text we want to transliterate)
 *
 * <code>
 * <?php
 * $text = "This is some text written using Cyrillic character set";
 *
 * $tl = new transliterate($text);
 *
 * // Text is translated and is being held in $string variable
 *
 * echo $transl->string; // Prints transliterated text
 * ?>
 * </code>
 *
 * 2. Using other methods for transliterating text
 *
 * 2.a:
 * <code>
 * $text = "This is some text written using Cyrillic character set";
 *
 * $tl = new transliterate;
 *
 * $translitereated_text = $tl->transliterate_return($text);
 *
 * echo $translitereated_text; // Prints transliterated text
 * </code>
 *
 * 2.b Using "pass by reference" to transliterate text directyle:
 *
 * <code>
 * $text = "This is some text written using Cyrillic character set";
 *
 * $tl = new transliterate;
 *
 * $tl->transliterate_ref($text);
 *
 * echo $text; // Prints transliterated text
 * </code>
 *
 * 2.c If you want to output the text directly:
 * <code>
 * $text = "This is some text written using Cyrillic character set";
 *
 * $tl = new transliterate;
 *
 * echo $tl->transliterate_return($text); // Prints transliterated text
 * </code>
 * @author Mihailo Joksimovic
 * @version 0.1
 */
/**
 * Class body
 * @package transliterate
 */

class Xmltv_Cyrillic_Translit
{
    /**
     * Class constructor. Can be called with, or without arguments.
     *
     * Examples:
     *
     * <code>
     * <?php
     * $tl = new transliterate; // Creating instance without argument
     * $tl = new transliterate("Cyrillic text goes here"); // Creating instance with argument
     * ?>
     * </code>
     * @param integer $str This is the text we want to transliterate
     * @see string, transliterate
     *
     */
    function transliterate() // Constructor
    {
        if (func_num_args() == 1)
        {
            $args = func_get_args();
            $str = $args[0];
            $this->string = str_replace($this->niddle, $this->replace, $str);
        }
        else
        {
            return;
        }
    }

    /**
     * This function returns translated text to be stored in variable or echoed :-)
     *
     * Example 1:
     * <code>
     * $text = "This is some text written using Cyrillic character set";
     *
     * $tl = new transliterate;
     *
     * $translitereated_text = $tl->transliterate_return($text);
     *
     * echo $translitereated_text; // Prints transliterated text
     * </code>
     * Example 2:
     * <code>
     * $text = "This is some text written using Cyrillic character set";
     *
     * $tl = new transliterate;
     *
     * echo $tl->transliterate_return($text); // Prints transliterated text
     * </code>
     *
     * @param string $str Text to be translated
     * @return string
     */
    public function transliterate_return($str)
    {
        $this->string = str_replace($this->niddle, $this->replace, $str);
        return $this->string;
    }

    /**
     * This function uses "pass by reference" method to directlty transliterate text
     * Better said, if you want to transliterate text stored in $text variable,
     * you should do something similar to this:
     * <code>
     * $text = "This is some text written using Cyrillic character set";
     *
     * $tl = new transliterate;
     *
     * $tl->transliterate_ref($text);
     *
     * echo $text; // Prints transliterated text
     * </code>
     * @param string &$str
     */
    public function transliterate_ref(&$str)
    {
        $this->string = str_replace($this->niddle, $this->replace, $str);
        $str = $this->string;
    }


    private $niddle = array("а", "б", "в", "г", "д", "ђ", "е", "ж", "з", "и", "ј", "к", "л", "љ", "м", "н",
    "њ", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "џ", "ш", "щ", "ь", "ю",
    "А", "Б", "В", "Г", "Д", "Ђ", "Е", "Ж", "З", "И", "Ј", "К", "Л", "Љ", "М", "Н",
    "Њ", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Џ", "Ш");

    private $replace = array ("a", "b", "v", "g", "d", "d", "e", "z", "z", "i", "j", "k", "l", "lj", "m", "n", "nj", "o", "p",
    "r", "s", "t", "c", "u", "f", "h", "c", "c", "dz", "s", "sch", "", "u",
    "A", "B", "B", "G", "D", "D", "E", "Z", "Z", "I", "J", "K", "L", "LJ", "M", "N", "NJ", "O", "P",
    "R", "S", "T", "C", "U", "F", "H", "C", "C", "DZ", "S"
    );

    /**
     * Transliterated text is always saved in this variable, so you can use
     * it numerous times ... :-)
     *
     * @var string
     * @access public
     *
     */
    public $string;
}
?>