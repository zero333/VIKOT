<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
/**
 * Baseline rule class for extension into a "real" parser component.
 *
 * PHP versions 4 and 5
 *
 * @category   Text
 * @package    Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id: Parse.php,v 1.5 2005/07/29 08:57:29 toggg Exp $
 * @link       http://pear.php.net/package/Text_Wiki
 */

/**
 * Baseline rule class for extension into a "real" parser component.
 *
 * Text_Wiki_Rule classes do not stand on their own; they are called by a
 * Text_Wiki object, typcially in the transform() method. Each rule class
 * performs three main activities: parse, process, and render.
 *
 * The parse() method takes a regex and applies it to the whole block of
 * source text at one time. Each match is sent as $matches to the
 * process() method.
 *
 * The process() method acts on the matched text from the source, and
 * then processes the source text is some way.  This may mean the
 * creation of a delimited token using addToken().  In every case, the
 * process() method returns the text that should replace the matched text
 * from parse().
 *
 * @category   Text
 * @package    Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Wiki
 */
class Text_Wiki_Parse {


    /**
    *
    * Configuration options for this parser rule.
    *
    * @access public
    *
    * @var string
    *
    */

    var $conf = array();


    /**
    *
    * Regular expression to find matching text for this rule.
    *
    * @access public
    *
    * @var string
    *
    * @see parse()
    *
    */

    var $regex = null;


    /**
    *
    * The name of this rule for new token array elements.
    *
    * @access public
    *
    * @var string
    *
    */

    var $rule = null;


    /**
    *
    * A reference to the calling Text_Wiki object.
    *
    * This is needed so that each rule has access to the same source
    * text, token set, URLs, interwiki maps, page names, etc.
    *
    * @access public
    *
    * @var object
    */

    var $wiki = null;


    /**
    *
    * Constructor for this parser rule.
    *
    * @access public
    *
    * @param object &$obj The calling "parent" Text_Wiki object.
    *
    */

    function Text_Wiki_Parse(&$obj)
    {
        // set the reference to the calling Text_Wiki object;
        // this allows us access to the shared source text, token
        // array, etc.
        $this->wiki = $obj;

        // set the name of this rule; generally used when adding
        // to the tokens array. strip off the Text_Wiki_Parse_ portion.
        // text_wiki_parse_
        // 0123456789012345
        $tmp = substr(get_class($this), 16);
        $this->rule = ucwords(strtolower($tmp));

        // override config options for the rule if specified
        if (isset($this->wiki->parseConf[$this->rule]) &&
            is_array($this->wiki->parseConf[$this->rule])) {

            $this->conf = array_merge(
                $this->conf,
                $this->wiki->parseConf[$this->rule]
            );

        }
    }


    /**
    *
    * Abstrct method to parse source text for matches.
    *
    * Applies the rule's regular expression to the source text, passes
    * every match to the process() method, and replaces the matched text
    * with the results of the processing.
    *
    * @access public
    *
    * @see Text_Wiki_Parse::process()
    *
    */

    function parse()
    {
        $this->wiki->source = preg_replace_callback(
            $this->regex,
            array(&$this, 'process'),
            $this->wiki->source
        );
    }


    /**
    *
    * Abstract method to generate replacements for matched text.
    *
    * @access public
    *
    * @param array $matches An array of matches from the parse() method
    * as generated by preg_replace_callback.  $matches[0] is the full
    * matched string, $matches[1] is the first matched pattern,
    * $matches[2] is the second matched pattern, and so on.
    *
    * @return string The processed text replacement; defaults to the
    * full matched string (i.e., no changes to the text).
    *
    * @see Text_Wiki_Parse::parse()
    *
    */

    function process(&$matches)
    {
        return $matches[0];
    }


    /**
    *
    * Simple method to safely get configuration key values.
    *
    * @access public
    *
    * @param string $key The configuration key.
    *
    * @param mixed $default If the key does not exist, return this value
    * instead.
    *
    * @return mixed The configuration key value (if it exists) or the
    * default value (if not).
    *
    */

    function getConf($key, $default = null)
    {
        if (isset($this->conf[$key])) {
            return $this->conf[$key];
        } else {
            return $default;
        }
    }


    /**
    *
    * Extract 'attribute="value"' portions of wiki markup.
    *
    * This kind of markup is typically used only in macros, but is useful
    * anywhere.
    *
    * The syntax is pretty strict; there can be no spaces between the
    * option name, the equals, and the first double-quote; the value
    * must be surrounded by double-quotes.  You can escape characters in
    * the value with a backslash, and the backslash will be stripped for
    * you.
    *
    * @access public
    *
    * @param string $text The "attributes" portion of markup.
    *
    * @return array An associative array of key-value pairs where the
    * key is the option name and the value is the option value.
    *
    */

    function getAttrs($text)
    {
        // find the =" sections;
        $tmp = explode('="', trim($text));

        // basic setup
        $k = count($tmp) - 1;
        $attrs = array();
        $key = null;

        // loop through the sections
        foreach ($tmp as $i => $val) {

            // first element is always the first key
            if ($i == 0) {
                $key = trim($val);
                continue;
            }

            // find the last double-quote in the value.
            // the part to the left is the value for the last key,
            // the part to the right is the next key name
            $pos = strrpos($val, '"');
            $attrs[$key] = stripslashes(substr($val, 0, $pos));
            $key = trim(substr($val, $pos+1));

        }

        return $attrs;

    }
}
?>
