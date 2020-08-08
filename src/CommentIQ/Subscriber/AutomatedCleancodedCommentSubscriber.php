<?php


class CommentIQ_Subscriber_AutomatedCleancodedCommentSubscriber implements CommentIQ_EventManagement_SubscriberInterface
{
    /**
     * The post meta key used to store whether the post has disabled automatic insertion
     * of the cleancoded comment or not.
     *
     * @var string
     */
    private $disabled_cleancoded_comment_meta_key;

    /**
     * The cleancoded comment generator.
     *
     * @var CommentIQ_Generator_CleancodedCommentGenerator
     */
    private $cleancoded_comment_generator;

    /**
     * The post types that we want to insert Cleancoded Comments into.
     *
     * @var array
     */
    private $post_types;

    /**
     * Constructor.
     *
     * @param CommentIQ_Generator_CleancodedCommentGenerator $cleancoded_comment_generator
     * @param array                                        $post_types
     */
    public function __construct(CommentIQ_Generator_CleancodedCommentGenerator $cleancoded_comment_generator, array $post_types = array())
    {
        $this->disabled_cleancoded_comment_meta_key = 'commentiq_disable_cleancoded_comment';
        $this->cleancoded_comment_generator = $cleancoded_comment_generator;
        $this->post_types = $post_types;
    }

    /**
     * {@inheritdoc}
     */
    public static function get_subscribed_events()
    {
        return array(
            'post_comment_status_meta_box-options' => 'add_disable_option',
            'save_post' => 'save_disable_option',
            'the_content' => 'insert_cleancoded_comment',
        );
    }

    /**
     * Add an option to the "Discussion" meta box that allows someone to disable
     * automatic insertion of the cleancoded comment.
     *
     * @param WP_Post $post
     */
    public function add_disable_option(WP_Post $post)
    {
        $format = '<br /><label for="%1$s" class="selectit"><input name="%1$s" type="checkbox" id="%1$s" %2$s/> %3$s</label>';
        echo sprintf($format, $this->disabled_cleancoded_comment_meta_key, checked($this->is_automatic_insertion_disabled($post), true, false), __('Disable automatic insertion of cleancoded comment.', 'commentiq'));
    }

    /**
     * Insert the cleancoded comment into the content of a post about 30%
     * of the way through. Will insert it before going over the 30% threshold.
     *
     * @param string $content
     *
     * @return string
     */
    public function insert_cleancoded_comment($content)
    {
        
        if (!is_singular($this->post_types)
            || has_shortcode($content, CommentIQ_Shortcode_CleancodedCommentShortcode::get_name())
        ) {
            return $content;
        }

        $post = get_post();

        if (!$post instanceof WP_Post
            || !in_array($post->post_type, $this->post_types)
            || $this->is_automatic_insertion_disabled($post)
        ) {
            return $content;
        }

        $content_word_count = str_word_count(strip_tags($content));
        $inserted = false;
        $content_blocks = array();
        $new_content = '';
        
        if ( $content_word_count <= 0 ) {
            return $content;
        }
        
        $content_array = explode( "\n", $content );
        foreach( $content_array as $content_block ) {
             $content_block = trim( $content_block );
             $new_content .= $content_block;
             
             $new_content_word_count = str_word_count(strip_tags($new_content));
            $new_percentage = (str_word_count(strip_tags($content_block)) + $new_content_word_count) / $content_word_count;
            $old_percentage = $new_content_word_count / $content_word_count;
            $percentage_threshold = 0.3;

            if (!$inserted && $new_percentage > $percentage_threshold && $old_percentage < $percentage_threshold ) {
                 
                 $html_tags = '(table|div|dl|ul|ol|pre|form|blockquote|address|math|p|h[1-6]|hr|fieldset|select)';
                 $regex = '/<\/' . $html_tags . '>$/i';
                 if( preg_match( $regex, $content_block ) ) {
                      $new_content .= $this->cleancoded_comment_generator->generate($post);
                    $inserted = true;
                 }
                
            }
        }
        return $new_content;
    }

    /**
     * Saves the option from the "Discussion" meta box that allows someone to disable
     * automatic insertion of the cleancoded comment.
     *
     * @param int $post_id
     */
    public function save_disable_option($post_id)
    {
        if ((defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
            || empty($_POST['post_type'])
            || !current_user_can('edit_' . $_POST['post_type'], $post_id)
        ) {
            return;
        }

        if (empty($_POST[$this->disabled_cleancoded_comment_meta_key])) {
            delete_post_meta($post_id, $this->disabled_cleancoded_comment_meta_key);
        } elseif ('on' === $_POST[$this->disabled_cleancoded_comment_meta_key]) {
            update_post_meta($post_id, $this->disabled_cleancoded_comment_meta_key, 1);
        }
    }

    /**
     * Checks whether the given post has disabled the automatic insertion
     * of Cleancoded Comments.
     *
     * @param WP_Post $post
     *
     * @return bool
     */
    private function is_automatic_insertion_disabled(WP_Post $post)
    {
        $disabled = get_post_meta($post->ID, $this->disabled_cleancoded_comment_meta_key, true);

        return !empty($disabled);
    }
}
