<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
/**
 * Wikilink rule end renderer for Xhtml
 *
 * PHP versions 4 and 5
 *
 * @category   Text
 * @package    Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id: Wikilink.php,v 1.17 2006/02/28 03:15:09 justinpatrin Exp $
 * @link       http://pear.php.net/package/Text_Wiki
 */

/**
 * This class renders wiki links in XHTML.
 *
 * @category   Text
 * @package    Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Wiki
 */
class Text_Wiki_Render_Xhtml_Wikilink extends Text_Wiki_Render {

    var $conf = array(
        'pages' => array(), // set to null or false to turn off page checks
        'view_url' => 'http://example.com/index.php?page=%s',
        'new_url'  => 'http://example.com/new.php?page=%s',
        'new_text' => '?',
        'new_text_pos' => 'after', // 'before', 'after', or null/false
        'css' => null,
        'css_new' => null,
        'exists_callback' => null // call_user_func() callback
    );


    /**
    *
    * Renders a token into XHTML.
    *
    * @access public
    *
    * @param array $options The "options" portion of the token (second
    * element).
    *
    * @return string The text rendered from the token options.
    *
    */

    function token($options)
    {
        // make nice variable names (page, anchor, text)
        extract($options);

        // is there a "page existence" callback?
        // we need to access it directly instead of through
        // getConf() because we'll need a reference (for
        // object instance method callbacks).
        if (isset($this->conf['exists_callback'])) {
            $callback = $this->conf['exists_callback'];
        } else {
        	$callback = false;
        }

        if ($callback) {
            // use the callback function
            $exists = call_user_func($callback, $page);
        } else {
            // no callback, go to the naive page array.
            $list = $this->getConf('pages');
            if (is_array($list)) {
                // yes, check against the page list
                $exists = in_array($page, $list);
            } else {
                // no, assume it exists
                $exists = true;
            }
        }

        // convert *after* checking against page names so as not to mess
        // up what the user typed and what we're checking.
        $page = $this->urlEncode($page);
        $anchor = $this->urlEncode($anchor);
        $text = $this->textEncode($text);

        // does the page exist?
        if ($exists) {

            // PAGE EXISTS.

            // link to the page view, but we have to build
            // the HREF.  we support both the old form where
            // the page always comes at the end, and the new
            // form that uses %s for sprintf()
            $href = $this->getConf('view_url');

            if (strpos($href, '%s') === false) {
                // use the old form (page-at-end)
                $href = $href . $page . $anchor;
            } else {
                // use the new form (sprintf format string)
                $href = sprintf($href, $page . $anchor);
            }

            // get the CSS class and generate output
            $css = $this->formatConf(' class="%s"', 'css');

            $start = '<a'.$css.' href="'.$href.'">';
            $end = '</a>';
        } else {

            // PAGE DOES NOT EXIST.

            // link to a create-page url, but only if new_url is set
            $href = $this->getConf('new_url', null);

            // set the proper HREF
            if (! $href || trim($href) == '') {

                // no useful href, return the text as it is
                $output = $text;

            } else {

                // yes, link to the new-page href, but we have to build
                // it.  we support both the old form where
                // the page always comes at the end, and the new
                // form that uses sprintf()
                if (strpos($href, '%s') === false) {
                    // use the old form
                    $href = $href . $page;
                } else {
                    // use the new form
                    $href = sprintf($href, $page);
                }
            }

            // get the appropriate CSS class and new-link text
            $css = $this->formatConf(' class="%s"', 'css_new');
            $new = $this->getConf('new_text');

            // what kind of linking are we doing?
            $pos = $this->getConf('new_text_pos');
            if (! $pos || ! $new) {
                // no position (or no new_text), use css only on the page name

                //do we really want this? it breaks the text output of the output
                //  if the page does not exist and the link text != page name
                $text = $page;
                $start = '<a'.$css.' href="'.$href.'">';
                $end = '</a>';
            } elseif ($pos == 'before') {
                // use the new_text BEFORE the page name
                $start = '<a'.$css.' href="'.$href.'">'.$new.'</a>';
                $end = '';
            } else {
                // default, use the new_text link AFTER the page name
                $start = '';
                $end = '<a'.$css.' href="'.$href.'">'.$new.'</a>';
            }
        }
        if (!strlen($text)) {
            $start .= $this->textEncode($options['page']);
        }
        if (isset($type)) {
            switch ($type) {
            case 'start':
                $output = $start;
                break;
            case 'end':
                $output = $end;
                break;
            }
        } else {
            $output = $start.$text.$end;
        }
        return $output;
    }
}
?>
