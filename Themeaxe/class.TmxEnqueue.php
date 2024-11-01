<?php
/**
 * Class TmxEnqueue
 * Enqueue Style & Scripts
 *
 * @package     WPRevisionMasterPlugin
 * @author      Md. Hasan Shahriar <info@themeaxe.com>
 * @since       1.0.1
 */

namespace Themeaxe;

class TmxEnqueue
{
    /**
     * TmxEnqueue constructor.
     *
     * @since 1.0.1
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Enqueues style & scripts
     *
     * @since 1.0.1
     */
    public function tmx_enqueue_scripts()
    {
    }

    /**
     * Enqueues style & scripts on admin
     *
     * @since 1.0.1
     */
    public function tmx_admin_scripts()
    {
        wp_enqueue_style('tmx-revision-master-admin', TMXRM_URI.'assets/css/admin-style.css');
        wp_enqueue_script( 'wp-notice', TMXRM_URI.'assets/js/wp-notices.js', array(), '', true );
        wp_enqueue_script( 'revision-master', TMXRM_URI.'assets/js/revision-master-post.js', array( 'jquery' ), '', true );
    }

    /**
     * Attach hooks
     *
     * @since 1.0.1
     */
    private function hooks()
    {
        add_action( 'wp_enqueue_scripts', array($this, 'tmx_enqueue_scripts') );
        add_action( 'admin_enqueue_scripts', array($this, 'tmx_admin_scripts') );
    }
}