<?php

namespace FluxBB\CommonMark\Common;

/**
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class Text implements \Serializable
{

    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text = '')
    {
        $this->text = (string) $text;
    }

    /**
     * @param string $text
     *
     * @return Text
     */
    public function append($text)
    {
        $this->text .= (string) $text;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return Text
     */
    public function prepend($text)
    {
        $this->text = $text . $this->text;

        return $this;
    }

    /**
     * Surround text with given string
     *
     * @param string $start
     * @param string $end
     *
     * @return Text
     */
    public function wrap($start, $end)
    {
        $this->text = $start . $this->text . $end;

        return $this;
    }

    /**
     * Make a string lowercase
     *
     * @return Text
     */
    public function lower()
    {
        $this->text = mb_strtolower($this->text);

        return $this;
    }

    /**
     * Make a string uppercase
     *
     * @return Text
     */
    public function upper()
    {
        $this->text = strtoupper($this->text);

        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of a string
     *
     * @param string $charList Optionally, the stripped characters can also be specified using the charlist parameter.
     *                          Simply list all characters that you want to be stripped. With .. you can specify a range of characters.
     *
     * @return Text
     */
    public function trim($charList = null)
    {
        if (is_null($charList)) {
            $this->text = trim($this->text);
        } else {
            $this->text = trim($this->text, $charList);
        }

        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the end of a string
     *
     * @param string $charList You can also specify the characters you want to strip, by means of the charlist parameter.
     *                          Simply list all characters that you want to be stripped. With .. you can specify a range of characters.
     *
     * @return Text
     */
    public function rtrim($charList = null)
    {
        if (is_null($charList)) {
            $this->text = rtrim($this->text);
        } else {
            $this->text = rtrim($this->text, $charList);
        }

        return $this;
    }

    /**
     * @param string $charList You can also specify the characters you want to strip, by means of the charlist parameter.
     *                          Simply list all characters that you want to be stripped. With .. you can specify a range of characters.
     *
     * @since  1.1
     * @return Text
     */
    public function ltrim($charList = null)
    {
        if (is_null($charList)) {
            $this->text = ltrim($this->text);
        } else {
            $this->text = ltrim($this->text, $charList);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->text === '';
    }

    /**
     * Checks whether the text contains $text
     *
     * @param string $text A string to test
     *
     * @return bool True if the text contains $text
     */
    public function contains($text)
    {
        return strpos($this->text, $text) !== false;
    }

    /**
     * Checks whether the text begins with $text
     *
     * @param string $text
     *
     * @return bool
     */
    public function startsWith($text)
    {
        return strpos($this->text, $text) === 0;
    }

    /**
     * Checks whether the text ends with $text
     *
     * @param string $text
     *
     * @return bool
     */
    public function endsWith($text)
    {
        return $text === substr($this->text, -strlen($text));
    }

    /**
     * Convert special characters to HTML entities
     *
     * @param int $option
     *
     * @return Text
     */
    public function escapeHtml($option = ENT_QUOTES)
    {
        $this->text = htmlspecialchars($this->text, $option, 'UTF-8', true);

        return $this;
    }

    /**
     * Convert HTML entities to their corresponding UTF-8 characters.
     *
     * @return Text
     */
    public function decodeEntities()
    {
        $this->text = html_entity_decode($this->text, ENT_NOQUOTES | ENT_HTML5, 'UTF-8');

        return $this;
    }

    /**
     * Escape special characters for use in an URL.
     *
     * @return Text
     */
    public function encodeUrl()
    {
        $ignore = [
            '%21' => '!',
            '%23' => '#',
            '%24' => '$',
            '%26' => '&',
            '%27' => '\'',
            '%28' => '(',
            '%29' => ')',
            '%2A' => '*',
            '%2B' => '+',
            '%2C' => ',',
            '%2D' => '-',
            '%2E' => '.',
            '%2F' => '/',
            '%3A' => ':',
            '%3B' => ';',
            '%3D' => '=',
            '%3F' => '?',
            '%40' => '@',
            '%5F' => '_',
            '%7E' => '~',
        ];

        $this->text = strtr(rawurlencode(rawurldecode($this->text)), $ignore);

        return $this;
    }

    /**
     * Perform a regular expression search and replace
     *
     * @param string          $pattern     The pattern to search for. It can be either a string or an array with strings.
     * @param string|callable $replacement The string or an array with strings to replace.
     *                                     If $replacement is the callable, a callback that will be called and passed an array of matched elements in the subject string.
     *
     * @return Text
     */
    public function replace($pattern, $replacement)
    {
        if (is_callable($replacement)) {
            $this->text = preg_replace_callback($pattern, function ($matches) use ($replacement) {
                $args = array_map(function ($item) {
                    return new Text($item);
                }, $matches);

                return call_user_func_array($replacement, $args);
            }, $this->text);
        } else {
            $this->text = preg_replace($pattern, $replacement, $this->text);
        }

        return $this;
    }

    /**
     * Find the given regular expression and execute callbacks on it and its surroundings.
     *
     * @param string   $pattern       The pattern to search for. It can be either a string or an array with strings.
     * @param callable $resultHandler A callback that will be passed an array of matched elements in the subject string.
     * @param callable $partHandler   A callback that will be passed any remaining parts of the subject string.
     *
     * @return Text
     */
    public function handle($pattern, $resultHandler, $partHandler)
    {
        $nodes = [];

        $text = preg_replace_callback($pattern, function ($matches) use (&$nodes) {
            $nodes[] = $matches;

            return "\0";
        }, $this->text);

        $parts = explode("\0", $text);

        $match = 0;
        foreach ($nodes as $node) {
            if ($parts[$match] !== '') $partHandler(new static($parts[$match]));

            $args = array_map(function ($item) {
                return new static($item);
            }, $node);

            call_user_func_array($resultHandler, $args);

            $match++;
        }

        if ($parts[$match] !== '') $partHandler(new static($parts[$match]));

        return $this;
    }

    /**
     * Replace all occurrences of the search string with the replacement string
     *
     * @param string|array $search  The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
     * @param string|array $replace The replacement value that replaces found search values. An array may be used to designate multiple replacements.
     *
     * @return Text
     */
    public function replaceString($search, $replace)
    {
        $this->text = str_replace($search, $replace, $this->text);

        return $this;
    }

    /**
     * Perform a regular expression match
     *
     * @param string     $pattern The pattern to search for, as a string.
     * @param array|null $matches If matches is provided, then it is filled with the results of search.
     *
     * @return boolean
     */
    public function match($pattern, &$matches = null)
    {
        return preg_match($pattern, $this->text, $matches) > 0;
    }

    /**
     * Split string by a regular expression
     *
     * @param string $pattern The pattern to search for, as a string.
     * @param int    $flags   [optional] PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_OFFSET_CAPTURE
     *
     * @return Text[]|Collection
     */
    public function split($pattern, $flags = PREG_SPLIT_DELIM_CAPTURE)
    {
        return new Collection(
            array_map(function ($item) {
                return new static($item);
            }, preg_split($pattern, $this->text, -1, $flags))
        );
    }

    /**
     * Gets the length of a string
     *
     * @return int Returns the number of characters in string str having character encoding encoding.
     *             A multi-byte character is counted as 1.
     */
    public function getLength()
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($this->text, 'UTF-8');
        }

        // @codeCoverageIgnoreStart
        return preg_match_all("/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", $this->text);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Set internal string value
     *
     * @param Text
     */
    public function setString($text)
    {
        $this->text = (string) $text;
    }

    /**
     * Returns internal string value
     *
     * @return string
     */
    public function getString()
    {
        return $this->text;
    }

    /**
     * Copy this object.
     *
     * @return Text
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->text);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized The string representation of the object.
     */
    public function unserialize($serialized)
    {
        $this->text = unserialize($serialized);
    }

}
