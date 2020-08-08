<?php

class CommentIQ_Generator_CleancodedCommentGenerator
{
    /**
     * The post meta key used to store the cleancoded comment ID.
     *
     * @var string
     */
    private $cleancoded_comment_id_meta_key;

    /**
     * The post types that we want to insert Cleancoded Comments into.
     *
     * @var array
     */
    private $post_types;

    /**
     * Path to the default template used by the cleancoded comment generator.
     *
     * @var string
     */
    private $default_template_path;

    /**
     * Constructor.
     *
     * @param string $default_template_path
     * @param array  $post_types
     */
    public function __construct($default_template_path, array $post_types = array())
    {
        $this->cleancoded_comment_id_meta_key = 'commentiq_cleancoded_comment_id';
        $this->post_types = $post_types;
        $this->default_template_path = $default_template_path;
    }

    /**
     * Generate the cleancoded comment HTML for the given post.
     *
     * @param WP_Post $post
     *
     * @return string
     */
    public function generate(WP_Post $post)
    {
        if (!in_array($post->post_type, $this->post_types)) {
            return '';
        }

        $cleancoded_comment_id = get_post_meta($post->ID, 'commentiq_cleancoded_comment_id', true);

        if (!is_numeric($cleancoded_comment_id)) {
            return '';
        }

        $cleancoded_comment = get_comment($cleancoded_comment_id);
        
		/**
		 * Filter: cleancoded_allow_post_author
		 *
		 * Whether to allow post author comments to be cleancoded.
		 *
		 * @since 1.1.6
		 *
		 * @param bool false if not, true if yes
		 */
        $allow_post_author  = apply_filters( 'cleancoded_allow_post_author', false );

        if ( ! $cleancoded_comment instanceof WP_Comment || ! $this->is_valid_comment($cleancoded_comment ) || ( ( $post->post_author === $cleancoded_comment->user_id ) && ! $allow_post_author ) ) {
            return '';
        }

        return $this->render_cleancoded_comment($cleancoded_comment);
    }

    /**
     * Get the PHP template that the cleancoded comment generator will use.
     *
     * @return string
     */
    private function get_template()
    {
        $template = get_query_template('commentiq-cleancoded-comment');

        if (empty($template)) {
            $template = $this->default_template_path;
        }

        return $template;
    }

    /**
     * Checks if the given comment is valid.
     *
     * @param WP_Comment $comment
     *
     * @return bool
     */
    private function is_valid_comment(WP_Comment $comment)
    {
        return '1' == $comment->comment_approved && empty($comment->comment_type);
    }

    /**
     * Renders the cleancoded comment.
     *
     * The method does this using out buffering. It'll buffer the output of
     * the cleancoded comment template that gets included. It'll then return
     * its output once done processing.
     *
     * @param WP_Comment $comment
     *
     * @return string
     */
    private function render_cleancoded_comment(WP_Comment $comment)
    {
        $comment_show_in_content = (bool)CommentIQ_Admin_Settings::get_plugin_option( 'show_in_content' );
        
        /**
		 * Filter: cleancoded_show_in_content
		 *
		 * Whether to show the cleancoded comment.
		 *
		 * @since 1.1.1
         *
		 * @param bool  $comment_show_in_content true to show in content, false if not.
		 * @param int   Comment ID
		 * @param int   Comment Post ID
		 */
        $comment_show_in_content = (bool)apply_filters( 'cleancoded_show_in_content', $comment_show_in_content, $comment->comment_ID, $comment->comment_post_ID );
        
        if ( false === $comment_show_in_content ) {
            return;
        }
                
        ob_start();

        include $this->get_template();

        return ob_get_clean();
    }
}
