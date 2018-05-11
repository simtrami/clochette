<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\Helper;

/**
 * ShortCodesProcessor - shortcodes processor
 *
 * Migrated from WordPress's ShortCodes library
 *
 * @package ThinFrame\Foundation\ShortCodes
 * @since   0.2
 */
class ShortCodesProcessor
{
    /**
     * @var array registered shortcodes
     */
    protected $shortCodesTags = array();

    /**
     * Register short code
     *
     * @param string   $tag      tag name
     * @param callable $callback callback that will be triggered when tag will be processed
     *
     * @return $this
     */
    public function registerShortCode($tag, $callback)
    {
        if (is_callable($callback)) {
            $this->shortCodesTags[$tag] = $callback;
        }

        return $this;
    }

    /**
     * Remove short code
     *
     * @param string $tag tag to be removed
     *
     * @return $this
     */
    public function removeShortCode($tag)
    {
        if (isset($this->shortCodesTags[$tag])) {
            unset($this->shortCodesTags[$tag]);
        }

        return $this;
    }

    /**
     * Parse content
     *
     * @param string $content content to be parsed
     *
     * @return string
     */
    public function parseContent($content)
    {
        if (count($this->shortCodesTags) == 0) {
            return $content;
        }

        $context = $this;

        return preg_replace_callback(
            '/' . $this->generateShortCodesRegex() . '/s',
            function ($matches) use ($context) {
                return $context->parseShortCodeTag($matches);
            },
            $content
        );
    }

    /**
     * Generates shortcodes regex
     *
     * @return string
     */
    protected function generateShortCodesRegex()
    {
        $tags                  = array_keys($this->shortCodesTags);
        $regularExpression = join('|', array_map('preg_quote', $tags));

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\[' // Opening bracket
            . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($regularExpression)" // 2: Shortcode name
            . '(?![\\w-])' // Not followed by word character or hyphen
            . '(' // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*' // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])' // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*' // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)' // 4: Self closing tag ...
            . '\\]' // ... and closing bracket
            . '|'
            . '\\]' // Closing bracket
            . '(?:'
            . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+' // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+' // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]' // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }

    /**
     * Parse short code tag
     *
     * @param array $matches
     *
     * @return string
     */
    public function parseShortCodeTag($matches)
    {
        // allow [[foo]] syntax for escaping a tag
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $tag        = $matches[2];
        $attributes = $this->parseShortCodeAttributes($matches[3]);

        if (isset($matches[5])) {
            // enclosing tag - extra parameter
            return $matches[1] . call_user_func(
                $this->shortCodesTags[$tag],
                $attributes,
                $matches[5],
                $tag,
                $this
            ) . $matches[6];
        } else {
            // self-closing tag
            return $matches[1] . call_user_func(
                $this->shortCodesTags[$tag],
                $attributes,
                null,
                $tag,
                $this
            ) . $matches[6];
        }
    }

    /**
     * Parse short code attributes
     *
     * @param string $text short code attributes
     *
     * @return array|string
     */
    protected function parseShortCodeAttributes($text)
    {
        $attributes = array();
        $pattern    =
            '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s' .
            '|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text       = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!empty($match[1])) {
                    $attributes[strtolower($match[1])] = stripcslashes($match[2]);
                } elseif (!empty($match[3])) {
                    $attributes[strtolower($match[3])] = stripcslashes($match[4]);
                } elseif (!empty($match[5])) {
                    $attributes[strtolower($match[5])] = stripcslashes($match[6]);
                } elseif (isset($match[7]) and strlen($match[7])) {
                    $attributes[] = stripcslashes($match[7]);
                } elseif (isset($match[8])) {
                    $attributes[] = stripcslashes($match[8]);
                }
            }
        } else {
            $attributes = ltrim($text);
        }

        return $attributes;
    }
}
