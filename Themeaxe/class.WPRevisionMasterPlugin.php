<?php
/**
 * Class WPRevisionMasterPlugin
 * Control post revisions in WordPress
 *
 * @package     WPRevisionMasterPlugin
 * @author      Md. Hasan Shahriar <info@themeaxe.com>
 * @since       1.0.1
 */

namespace Themeaxe;

class WPRevisionMasterPlugin
{
    /**
     * @access      private
     * @var         \WPRevisionMasterPlugin $instance The one true WPRevisionMasterPlugin
     * @since       1.0.1
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.1
     * @return      self::$instance The one true WPRevisionMasterPlugin
     */
    public static function instance()
    {
        if ( ! self::$instance ) {
            self::$instance = new self;
            self::$instance->hooks();
            self::$instance->includes();
            self::$instance->admin_init();
        }
        return self::$instance;
    }

    /**
     * Run action and filter hooks
     *
     * @access      private
     * @since       1.0.1
     * @return      void
     */
    private function hooks()
    {
        add_action( "admin_init", array($this, "load_plugin") );
        add_action( "wp_ajax_tmxrm_trash_revision",  array($this, "tmxrm_trash_revision") );
        add_action( "wp_ajax_tmxrm_trash_revision_selected",  array($this, "tmxrm_trash_revision_selected") );
        add_action( "wp_ajax_tmxrm_limit_single_revision",  array($this, "tmxrm_limit_single_revision") );
        add_action( "save_post",  array($this, "tmxrm_limit_single_revision") );

        add_filter( "plugin_row_meta", array($this, "plugin_metalinks" ), null, 2 );
        add_filter( "wp_revisions_to_keep", array($this, "tmxrm_revisions_to_keep_single"), 10, 2);
    }

    /**
     * Filter callback to limit single post revisions
     *
     * @param $num Number of revisions from metabox
     * @param $post Post object
     * @return int Number of revisions to keep
     * @internal param $id
     * @since 1.0.1
     */
    public function tmxrm_revisions_to_keep_single($num, $post)
    {
        $options = get_option( 'tmx_revision_options' );

        // Revisions enable
        if(isset($options['disable_revision_master'])){
            return false;
        }

        // Settings
        $limit = get_post_meta($post->ID, 'tmxrm_revision_limit', true);
        if(empty($limit) || $limit==0){
            $limit = isset($options['count_revision_posts'][$post->post_type])?$options['count_revision_posts'][$post->post_type]:$options['count_revision_master'];
        }

        return ($limit!==false)?$limit:$num;
    }

    /**
     * Trash single revision
     *
     * @since 1.0.1
     */
    public function tmxrm_trash_revision()
    {

        if(!isset($_POST['id'])){
            wp_send_json_error(array(
                'msg' => '<b>'.__('Error','themeaxe').': </b>'.__('Revision ID not found','themeaxe').'.'
            ));
        }
        else if ( !isset($_POST['wpnonce']) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || !wp_verify_nonce( $_POST['wpnonce'], 'trash-revision-'.$_POST['id'] ) ) {
            wp_send_json_error(array(
                'msg' => '<b>'.__('Error','themeaxe').': </b>'.__('Revision not deleted','themeaxe').'.'
            ));
        }
        else{
            $trash_id = sanitize_text_field( $_POST['id'] );
            wp_delete_post_revision($trash_id);

            wp_send_json_success(array(
                'msg' => __('The revision was deleted','themeaxe').'.'
            ));
        }

        wp_die();
    }

    /**
     * Trash selected revisions
     *
     * @since 1.0.1
     */
    public function tmxrm_trash_revision_selected()
    {

        if(!isset($_POST['post_id'])){
            wp_send_json_error(array(
                'msg' => '<b>'.__('Error','themeaxe').': </b>'.__('Post not found','themeaxe').'.'
            ));
        }
        else if ( !isset($_POST['wpnonce']) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || !wp_verify_nonce( $_POST['wpnonce'], 'trash-revision-'.$_POST['post_id'] ) ) {
            wp_send_json_error(array(
                'msg' => '<b>'.__('Error','themeaxe').': </b>'.__('Revision not deleted','themeaxe').'.'
            ));
        }
        else if(!isset($_POST['id'])){
            wp_send_json_error(array(
                'msg' => '<b>'.__('Error','themeaxe').': </b>'.__('Revision not found','themeaxe').'.'
            ));
        }
        else{
            $trash_id = $_POST['id'];
            foreach ($trash_id as $trash){
                wp_delete_post_revision($trash);
            }

            wp_send_json_success(array(
                'msg' => __('The revisions were deleted','themeaxe').'.'
            ));
        }

        wp_die();
    }

    /**
     * Limit single post revision
     *
     * @since 1.0.1
     */
    public function tmxrm_limit_single_revision()
    {

        $is_error = true;
        $msg = "Unknown.";

        if(!isset($_POST['post_ID'])){
            $msg = 'Post not found.';
        }
        else if ( !isset($_POST['tmxrm_revision_limit_wpnonce']) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || !wp_verify_nonce( $_POST['tmxrm_revision_limit_wpnonce'], 'limit-revision-'.$_POST['post_ID'] ) ) {
            $msg = 'Revision limit not set.';
        }
        else if(!isset($_POST['tmxrm_revision_limit'])){
            $msg = 'Limit invalid.';
        }
        else{
            $post_id = $_POST['post_ID'];
            $limit = (int)$_POST['tmxrm_revision_limit'];

            $current = get_post_meta($post_id, 'tmxrm_revision_limit', true);
            $is_set = update_post_meta($post_id, 'tmxrm_revision_limit', $limit);

            if($limit < -1){
                $msg = "Revision limit can not be lower than -1";
            }
            else if($is_set!==false) {
                $is_error = false;
                $msg = "Revision limit set to " . $limit . " for this post";

                // all revisions and (possibly) one autosave
                $revisions = wp_get_post_revisions( $post_id, array( 'order' => 'ASC' ) );

                // $limit = (int) (# of autosaves to save)
                $delete = count($revisions) - $limit;

                if ( $delete > 0 ){
                    $revisions = array_slice( $revisions, 0, $delete );

                    for ( $i = 0; isset($revisions[$i]); $i++ ) {
                        if ( false !== strpos( $revisions[$i]->post_name, 'autosave' ) ) {
                            continue;
                        }
                        wp_delete_post_revision( $revisions[$i]->ID );
                    }
                }
            }
            else if($current==$limit) {
                $msg = 'Revision limit not modified.';
            }
            else {
                $msg = 'Revision limit error.';
            }
        }

        if(isset($_POST['tmxrm_ajax'])){
            if($is_error){
                wp_send_json_error(array(
                    'msg' => '<b>'.__('Error','themeaxe').': </b>'.__($msg,'themeaxe')
                ));
            }
            else{
                wp_send_json_success(array(
                    'msg' => __($msg,'themeaxe')
                ));
            }
            wp_die();
        }
    }

    /**
     * Activate plugin
     *
     * @access      public
     * @since       1.0.2
     * @param       boolean $network_wide True if plugin is network activated, false otherwise
     * @return      void
     */
    public static function activate($network_wide)
    {
        add_option( "wp_revision_master_activate", true );
    }

    /**
     * Plugin loaded
     *
     * @access      public
     * @since       1.0.2
     * @return      void
     */
    public function load_plugin()
    {
        if ( is_admin() && get_option( "wp_revision_master_activate", false ) ) {
            delete_option( "wp_revision_master_activate" );

            wp_redirect( admin_url('options-general.php?page=tmx-revision-master') );
            exit;
        }
    }

    /**
     * Deactivate plugin
     *
     * @access      public
     * @since       1.0.1
     * @param       boolean $network_wide True if plugin is network activated, false otherwise
     * @return      void
     */
    public static function deactivate($network_wide)
    {

    }

    /**
     * Add settings action link to plugins page
     *
     * @access      public
     * @since       1.0.1
     * @param       $links
     * @return      array $links
     */
    public function add_action_links( $links )
    {
        return array_merge(
              array( 'tmxrm_plugin_settings' => '<a href="' . admin_url( 'plugins.php?page=' . '#' ) . '">' . __( 'Settings', 'themeaxe' ) . '</a>' ),
              $links
         );
    }

    /**
     * Edit plugin metalinks
     *
     * @access      public
     * @since       1.0.1
     *
     * @param       array  $links The current array of links
     * @param       string $file  A specific plugin row
     *
     * @return      array The modified array of links
     */
    public function plugin_metalinks( $links, $file )
    {
        if ( strpos( $file, 'wp-revision-master.php' ) !== false && is_plugin_active( $file ) ) {

            $new_links = array(
                '<a href="' . admin_url( 'options-general.php?page=tmx-revision-master' ) . '">' . __( 'Settings', 'themeaxe' ) . '</a>',
                '<a href="' . 'mailto:themeaxe@gmail.com" target="_blank">' . __( 'Help', 'themeaxe' ) . '</a>'
            );

            $links = array_merge( $links, $new_links );
        }

        return $links;
    }

    /**
     * Include necessary files
     *
     * @access      public
     * @since       1.0.1
     * @return      void
     */
    public function includes()
    {
        require_once (TMXRM_PATH. 'Themeaxe/class.RevisionMetabox.php');
        require_once (TMXRM_PATH. 'Themeaxe/class.TmxEnqueue.php');
        require_once (TMXRM_PATH. 'Themeaxe/class.RevisionAdmin.php');
    }

    /**
     * gets the current post type in the WordPress Admin
     *
     * @access      public
     * @since       1.0.1
     * @return string/null $typenow
     */
    public function get_current_post_type()
    {
        global $post, $typenow, $current_screen;
        //we have a post so we can just get the post type from that
        if ( $post && $post->post_type ) {
            return $post->post_type;
        }
        //check the global $typenow - set in admin.php
        elseif ( $typenow ) {
            return $typenow;
        }
        //check the global $current_screen object - set in sceen.php
        elseif ( $current_screen && $current_screen->post_type ) {
            return $current_screen->post_type;
        }
        //check the post_type query string
        elseif ( isset( $_REQUEST['post_type'] ) ) {
            return sanitize_key( $_REQUEST['post_type'] );
        }
        //lastly check if post ID is in query string
        elseif ( isset( $_REQUEST['post'] ) ) {
            return get_post_type( $_REQUEST['post'] );
        }
        //we do not know the post type!
        return null;
    }

    /**
     * Initialize class
     */
    public function admin_init()
    {
        global $pagenow;

        $current_post = self::get_current_post_type();
        $options = get_option( 'tmx_revision_options' );
        $disabled = isset($options['disable_revision_master'])?$options['disable_revision_master']:false;
        $post_types = isset($options['enable_revision_posts'])?$options['enable_revision_posts']:array();

        if(is_admin() && !$disabled) {
            new RevisionMetabox($post_types);
            if( in_array($current_post, $post_types) ){
                new TmxEnqueue();
            }
        }

        if(is_admin()){
            new RevisionAdmin();
        }
    }
}