<?php


class CommentIQ_Subscriber_PostmaticAssetsSubscriber implements CommentIQ_EventManagement_SubscriberInterface {
    /**
     * Path to the directory where the plugin assets are stored.
     *
     * @var string
     */
    private $assets_path;

    /**
     * Constructor.
     *
     * @param string $assets_path
     */
    public function __construct( $assets_path ) {
        $this->assets_path = $assets_path;
    }

    /**
     * {@inheritdoc}
     */
    public static function get_subscribed_events() {
        return array(
            'prompt/html_email/print_styles' => 'print_styles',
        );
    }

    /**
     * Echo styles for Postmatic HTML emails.
     */
    public function print_styles() {
        @readfile( path_join( $this->assets_path, 'css/cleancoded-comment.css' ) );
    }
}
