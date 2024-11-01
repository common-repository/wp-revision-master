<?php
/**
 * Class RevisionAdmin
 * Adds admin settings
 *
 * @package     WPRevisionMasterPlugin
 * @author      Md. Hasan Shahriar <info@themeaxe.com>
 * @since       1.0.1
 */

namespace Themeaxe;

class RevisionAdmin
{
    /**
     * Holds the values to be used in the fields callbacks
     * @var $options array
     */
    private $options;

    /**
     * Holds all registered post types in theme
     * @var $post_types array
     */
    private $post_types;

    /**
     * RevisionAdmin constructor.
     *
     * @since 1.0.1
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Revisions',
            'Revision Settings',
            'manage_options',
            'tmx-revision-master',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'tmx_revision_options' );
        ?>
        <div class="wrap">
            <h1><?php _e('Revision Settings', 'themeaxe'); ?></h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'revision_option_group' );
                do_settings_sections( 'revision-setting-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Callback for revision enabled post types
     */
    public function enable_revision_posts_field($args)
    {
        ?>
            <?php

            foreach ( $this->post_types  as $post_type ) {
                if(!in_array($post_type->name, array('attachment'))){
                    //if(post_type_supports( $post_type->name, 'revisions' )){

                        $check_val = isset($this->options[$args['label_for']]) && in_array($post_type->name, $this->options[$args['label_for']])?true:false;
                        ?>
                        <label>
                            <input type="checkbox" class="code" name="tmx_revision_options[<?php echo $args['label_for']; ?>][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked( 1, $check_val, true ); ?> /> <?php echo $post_type->label; ?>
                        </label><br />
                        <?php
                    //}
                }
            }
            ?>
            <p class="description"><?php _e('Enable individual post types.', 'themeaxe'); ?></p>
        <?php
    }

    /**
     * Callback for revision enabled field
     */
    public function enable_revision_master_field($args)
    {
        ?>
            <input name="tmx_revision_options[<?php echo esc_attr($args['label_for']); ?>]" id="<?php echo esc_attr($args['label_for']); ?>" type="checkbox" value="true" class="code" <?php checked( 1, isset( $this->options[esc_attr($args['label_for'])] ), true ); ?> />
            <?php _e('Disable All Post Revisions', 'themeaxe'); ?>
            <p class="description"><?php _e('Individual post or post type can be separately disabled.', 'themeaxe'); ?></p>
        <?php
    }

    /**
     * Callback for revision count field
     */
    public function count_revision_field($args)
    {
        ?>
        <input name="tmx_revision_options[<?php echo $args['label_for']; ?>]" id="<?php echo esc_attr($args['label_for']); ?>" min="-1" step="1" type="number" value="<?php echo esc_attr($this->options[$args['label_for']]); ?>" class="code" />
        <p class="description"><?php _e('Maximum number of post revisions to keep.', 'themeaxe'); ?></p>
        <?php
    }

    /**
     * Callback for count revision post types
     */
    public function count_revision_posts_field($args)
    {
        ?>
        <?php

        foreach ( $this->post_types  as $post_type ) {
            if(!in_array($post_type->name, array('attachment'))){
                //if(post_type_supports( $post_type->name, 'revisions' )){

                    $check_val = isset($this->options[$args['label_for']][$post_type->name])?(int)$this->options[$args['label_for']][$post_type->name]:$this->options['count_revision_master'];
                    ?>
                    <label>
                        <input name="tmx_revision_options[<?php echo $args['label_for']; ?>][<?php echo esc_attr($post_type->name); ?>]" min="-1" step="1" type="number" value="<?php echo esc_attr($check_val); ?>" class="code" /> <?php echo $post_type->label; ?>
                    </label><br />
                    <?php
                //}
            }
        }
        ?>
        <p class="description"><?php _e('Individual post type maximum number of revisions to keep.', 'themeaxe'); ?></p>
        <?php
    }

    /**
     * Sanitizes settings data
     *
     * @since 1.0.1
     */
    public function sanitize_data($data)
    {
        return $data;
    }

    /**
     * Fills the section with the desired content
     */
    public function settings_section()
    {
        // Section data
    }

    /**
     * Revision setting options
     *
     * @since 1.0.1
     */
    public function revision_options()
    {
        // Enable settings
        register_setting(
            'revision_option_group', // Option group
            'tmx_revision_options', // Option name
            array( $this, 'sanitize_data' ) // Sanitize
        );

        add_settings_section(
            'tmxrm_revision_master_1', // ID
            'Display', // Title
            array( $this, 'settings_section' ), // Callback
            'revision-setting-admin' // Page
        );

        add_settings_field(
            'disable_revision_master', // ID
            'Disable Post Revisions', // Title
            array( $this, 'enable_revision_master_field' ), // Callback
            'revision-setting-admin', // Page
            'tmxrm_revision_master_1',     // settings section
            array( 'label_for' => 'disable_revision_master' ) // extra args array
        );

        add_settings_field(
            'enable_revision_posts',   // ID
            'Enable Post Types',        // Title
            array($this, 'enable_revision_posts_field'), // Callback
            'revision-setting-admin',  // Page
            'tmxrm_revision_master_1',     // settings section
            array( 'label_for' => 'enable_revision_posts' ) // extra args array
        );

        add_settings_section(
            'tmxrm_revision_master_2', // ID
            'Revision counts', // Title
            array( $this, 'settings_section' ), // Callback
            'revision-setting-admin' // Page
        );

        add_settings_field(
            'count_revision_master', // ID
            'Maximum Revisions', // Title
            array( $this, 'count_revision_field' ), // Callback
            'revision-setting-admin', // Page
            'tmxrm_revision_master_2',     // settings section
            array( 'label_for' => 'count_revision_master' ) // extra args array
        );

        add_settings_field(
            'count_revision_posts', // ID
            'Maximum Revisions in Post Types', // Title
            array( $this, 'count_revision_posts_field' ), // Callback
            'revision-setting-admin', // Page
            'tmxrm_revision_master_2',     // settings section
            array( 'label_for' => 'count_revision_posts' ) // extra args array
        );

    }

    /**
     * Get all registered post types
     */
    public function list_post_types()
    {
        $args = array(
            'public' => true
        );
        $this->post_types = get_post_types( $args, 'objects' );
    }

    /**
     * Attach hooks
     *
     * @since 1.0.1
     */
    private function hooks()
    {
        add_action( 'admin_init', array( $this, 'revision_options' ) );
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'list_post_types' ) );
    }
}