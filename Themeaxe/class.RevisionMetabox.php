<?php
/**
 * Class RevisionMetabox
 * Add metabox to post
 *
 * @package     WPRevisionMasterPlugin
 * @author      Md. Hasan Shahriar <info@themeaxe.com>
 * @since       1.0.1
 */

namespace Themeaxe;

class RevisionMetabox
{

    /**
     * @var array Post types
     * @access private
     */
    private $post_type;

    /**
     * WPRevisionMetabox constructor.
     *
     * @access      public
     * @since       1.0.1
     */
    public function __construct($post_types = array('post'))
    {
        $this->hooks();
        $this->post_type = $post_types;
    }

    /**
     * Remove default metabox
     *
     * @access      public
     * @since       1.0.1
     */
    public function remove_meta()
    {
        foreach ($this->post_type as $type){
            remove_meta_box( 'revisionsdiv', $type, 'normal' );
        }
    }

    /**
     * Add meta box with revision buttons
     */
    public function add_meta_boxes($post_type, $post)
    {

        if(in_array($post_type, $this->post_type)) {
            add_meta_box(
                'tmxrm-revision-master',
                __('Revisions', 'themeaxe'),
                array($this, 'display'),
                $this->post_type,
                'advanced',
                'high'
            );
        }
    }


    /**
     * Meta box display callback.
     *
     * @param WP_Post $post Current post object.
     */
    public function display( $post )
    {

        $all = wp_get_post_revisions($post->ID);
        $count = count($all);
        ?>
        <input name="_wp_nonce" value="<?php echo wp_create_nonce( 'limit_revision_'.$post->ID ); ?>" type="hidden" />
            <table class="form-table">
               <tbody>
                   <tr>
                       <th scope="row">
                           <label for="tmxrm_revision_count_setting"><?php _e('Revision limit for this post: ','themeaxe'); ?></label>
                       </th>
                       <td>
                           <input name="tmxrm_revision_limit" step="1" min="-1" id="tmxrm_revision_count_setting" value="<?php echo get_post_meta($post->ID, 'tmxrm_revision_limit', true); ?>" class="small-text" type="number"> <?php _e('revisions','themeaxe'); ?>
                           <input type="hidden" id="tmxrm_revision_limit_wpnonce" name="tmxrm_revision_limit_wpnonce" value="<?php echo wp_create_nonce( 'limit-revision-'.$post->ID ); ?>" />
                       </td>
                       <td>
                           <p class="submit">
                               <a class="button button-primary button-limit-revision" data-post="<?php echo $post->ID; ?>"><?php _e('Save changes', 'themeaxe'); ?></a>
                           </p>
                       </td>
                   </tr>
               </tbody>
            </table>

            <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input class="tmxrm_checkall" type="checkbox" />
                    </td>
                    <th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
                        <a href="#">
                            <span><?php _e('Entry','themeaxe'); ?></span>
                        </a>
                    </th>
                    <th scope="col" id="actions" class="manage-column column-action column-primary">
                            <span class="templateside">
                                <a class="button button-small button-trash-revision-selected" data-post="<?php echo $post->ID; ?>" data-wpnonce="<?php echo wp_create_nonce( 'trash-revision-'.$post->ID ); ?>"><?php _e('Trash Selected', 'themeaxe'); ?></a>
                            </span>
                    </th>
                </tr>
            </thead>
            <tbody>

            <?php if($count==0): ?>
            <tr>
                <td colspan="3"><?php _e('No revisions yet!','themeaxe'); ?></td>
            </tr>
            <?php endif; ?>

            <?php
            // Count revisions
            $item_count = 1;
            foreach ($all as $rev): ?>
            <tr class="revision-post-<?php echo $item_count; ?>" <?php if($item_count==1) echo "title='".__('Current revision.','themeaxe')."'"; ?> >
                <td><input class="tmxrm_checkbox" type="checkbox" value="<?php echo $rev->ID; ?>" name="tmxrm_check[]" /></td>
                <td class="tmx-revision-info">
                    <?php echo get_avatar($rev->post_author, 24); ?>
                    <span>
                    <?php
                        echo get_user_by('id',$rev->post_author)->display_name.', ';

                        $timestamp = strtotime($rev->post_date);
                        printf( _x( '%s ago ', '%s = human-readable time difference', 'themeaxe' ), human_time_diff( $timestamp, current_time( 'timestamp' ) ) );
                        printf( "( <a href='%s'>%s @ %s</a> )", admin_url('revision.php?revision=').$rev->ID, date("M d,Y", $timestamp), date("H:i:s", $timestamp));

                        if(preg_match('/autosave/',$rev->post_name)) printf(" [%s]",_x('Autosave','themeaxe'));
                    ?>
                    </span>
                </td>
                <td class="templateside" class="tmx-revision-button">
                    <a class="button button-primary button-small" href="<?php echo admin_url('revision.php?revision=').$rev->ID; ?>" target="_blank"><?php _e('Compare','themeaxe'); ?></a>
                    <a class="button button-small" href="<?php echo admin_url('revision.php?revision='.$rev->ID.'&action=restore&_wpnonce='.wp_create_nonce( 'restore-post_'.$rev->ID )); ?>"><?php _e('Restore','themeaxe'); ?></a>
                    <a class="button button-small button-trash-revision" data-trash="<?php echo $rev->ID; ?>" data-wpnonce="<?php echo wp_create_nonce( 'trash-revision-'.$rev->ID ); ?>"><?php _e('Trash','themeaxe'); ?></a>
                </td>
            </tr>
            <?php $item_count++; endforeach; ?>

            </tbody>
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input class="tmxrm_checkall" type="checkbox" />
                    </td>
                    <th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
                        <a href="#">
                            <span><?php _e('Entry','themeaxe'); ?></span>
                        </a>
                    </th>
                    <th scope="col" id="actions" class="manage-column column-action column-primary">
                        <span class="templateside">
                            <a class="button button-small button-trash-revision-selected" data-post="<?php echo $post->ID; ?>" data-wpnonce="<?php echo wp_create_nonce( 'trash-revision-'.$post->ID ); ?>"><?php _e('Trash Selected', 'themeaxe'); ?></a>
                        </span>
                    </th>
                </tr>
            </tfoot>
        </table>
        <?php
    }

    /**
     * Save meta box content.
     *
     * @param int $post_id Post ID
     * @return int $post_id Post ID
     */
    public function save( $post_id )
    {
        // Intentionally left blank
    }

    /**
     * Add revision support to post types
     *
     * @access      public
     * @since       1.0.1
     * @return      void
     */
    public function add_post_type_support(){
        foreach ($this->post_type as $type){
            add_post_type_support( $type, 'revisions' );
        }
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
        add_action( 'add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2 );
        add_action( 'save_post', array($this, 'save' ), 10, 1);
        add_action( 'admin_init', array($this, 'add_post_type_support' ) );
        add_action( 'admin_menu',  array($this, 'remove_meta' ) );
    }
}
