<?php


interface CommentIQ_Shortcode_ShortcodeInterface
{
    /**
     * Get the tag name used by the shortcode.
     *
     * @return string
     */
    public static function get_name();

    /**
     * Generate the output of the shortcode.
     *
     * @param array|string  $attributes
     * @param string        $content
     *
     * @return string
     */
    public function generate_output($attributes, $content = '');
}
