<?php
/**
 * کلاس مدیریت دیتابیس
 */

if (!defined('ABSPATH')) {
    exit;
}

class CI_Database {
    private $wpdb;
    private $charset_collate;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * ایجاد جداول دیتابیس
     */
    public function create_tables() {
        $this->create_certificates_table();
    }

    /**
     * ایجاد جدول گواهینامه‌ها
     */
    private function create_certificates_table() {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            national_code VARCHAR(20) NOT NULL,
            student_code VARCHAR(50) NOT NULL,
            certificate_type VARCHAR(100) NOT NULL,
            issue_date_jalali VARCHAR(20) NOT NULL,
            certificate_code VARCHAR(100) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_certificate_code (certificate_code),
            INDEX idx_certificate_type (certificate_type),
            INDEX idx_national_code (national_code),
            INDEX idx_student_code (student_code)
        ) {$this->charset_collate};
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * اضافه کردن گواهینامه جدید
     */
    public function add_certificate($data) {
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . 'ci_certificates',
            array(
                'user_id' => intval($data['user_id']),
                'first_name' => sanitize_text_field($data['first_name']),
                'last_name' => sanitize_text_field($data['last_name']),
                'national_code' => sanitize_text_field($data['national_code']),
                'student_code' => sanitize_text_field($data['student_code']),
                'certificate_type' => sanitize_text_field($data['certificate_type']),
                'issue_date_jalali' => sanitize_text_field($data['issue_date_jalali']),
                'certificate_code' => sanitize_text_field($data['certificate_code']),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * گرفتن تمام گواهینامه‌های یک کاربر
     */
    public function get_user_certificates($user_id, $type = null) {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d",
            intval($user_id)
        );

        if (!empty($type)) {
            $query .= $this->wpdb->prepare(" AND certificate_type = %s", $type);
        }

        $query .= " ORDER BY created_at DESC";

        return $this->wpdb->get_results($query);
    }

    /**
     * گرفتن گواهینامه با کد گواهینامه
     */
    public function get_certificate_by_code($certificate_code) {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE certificate_code = %s",
                $certificate_code
            )
        );
    }

    /**
     * تایید گواهینامه با کد گواهینامه و کد ملی یا کد دانش‌پژوهی
     */
    public function verify_certificate($certificate_code, $verification_code) {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE certificate_code = %s AND (national_code = %s OR student_code = %s)",
                $certificate_code,
                $verification_code,
                $verification_code
            )
        );
    }

    /**
     * دریافت تمام گواهینامه‌ها (برای مدیریت)
     */
    public function get_all_certificates($type = null, $limit = 50, $offset = 0) {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        $query = "SELECT * FROM {$table_name}";

        if (!empty($type)) {
            $query .= $this->wpdb->prepare(" WHERE certificate_type = %s", $type);
        }

        $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";

        return $this->wpdb->get_results(
            $this->wpdb->prepare($query, $limit, $offset)
        );
    }

    /**
     * حذف گواهینامه
     */
    public function delete_certificate($certificate_id) {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        return $this->wpdb->delete(
            $table_name,
            array('id' => intval($certificate_id)),
            array('%d')
        );
    }

    /**
     * به‌روزرسانی گواهینامه
     */
    public function update_certificate($certificate_id, $data) {
        $table_name = $this->wpdb->prefix . 'ci_certificates';
        return $this->wpdb->update(
            $table_name,
            array(
                'first_name' => sanitize_text_field($data['first_name']),
                'last_name' => sanitize_text_field($data['last_name']),
                'national_code' => sanitize_text_field($data['national_code']),
                'student_code' => sanitize_text_field($data['student_code']),
                'certificate_type' => sanitize_text_field($data['certificate_type']),
                'issue_date_jalali' => sanitize_text_field($data['issue_date_jalali']),
                'certificate_code' => sanitize_text_field($data['certificate_code']),
            ),
            array('id' => intval($certificate_id)),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
    }
}
?>