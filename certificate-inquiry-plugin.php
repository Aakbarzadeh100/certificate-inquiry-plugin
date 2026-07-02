<?php
/**
 * Plugin Name: سیستم استعلام گواهینامه
 * Plugin URI: https://aakbarzadeh.ir
 * Description: سیستم استعلام گواهینامه های آموزشی وردپرس
 * Version: 1.0.0.1002
 * Author: ابوالفضل اکبرزاده
 * Author URI: https://aakbarzadeh.ir
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: certificate-inquiry
 * Domain Path: /languages
 */

// اگر مستقیم وارد شد، بیرون رود
if (!defined('ABSPATH')) {
    exit;
}

// ثوابت پلاگین
define('CERT_INQUIRY_VERSION', '1.0.0.1002');
define('CERT_INQUIRY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CERT_INQUIRY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CERT_INQUIRY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// بارگذاری فایل‌های اصلی
require_once CERT_INQUIRY_PLUGIN_DIR . 'includes/class-database.php';
require_once CERT_INQUIRY_PLUGIN_DIR . 'includes/class-admin.php';
require_once CERT_INQUIRY_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once CERT_INQUIRY_PLUGIN_DIR . 'includes/class-api.php';

// کلاس اصلی پلاگین
class Certificate_Inquiry_Plugin {
    private static $instance = null;
    public $admin;
    public $database;
    public $shortcodes;
    public $api;

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->database = new CI_Database();
        $this->admin = new CI_Admin();
        $this->shortcodes = new CI_Shortcodes();
        $this->api = new CI_API();

        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this->database, 'create_tables'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'certificate-inquiry',
            false,
            dirname(CERT_INQUIRY_PLUGIN_BASENAME) . '/languages'
        );
    }

    public function deactivate_plugin() {
        flush_rewrite_rules();
    }
}

// راه‌اندازی پلاگین
Certificate_Inquiry_Plugin::get_instance();
?>