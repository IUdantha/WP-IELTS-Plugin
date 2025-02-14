<?php
/**
 * Plugin Name: IELTS Exam Simulator
 * Description: A plugin to simulate the IELTS exam with MCQ questions.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Activation Hook to Create Database Table
function ielts_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ielts_questions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        question TEXT NOT NULL,
        option_a TEXT NOT NULL,
        option_b TEXT NOT NULL,
        option_c TEXT NOT NULL,
        option_d TEXT NOT NULL,
        correct_answer VARCHAR(10) NOT NULL,
        description TEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'ielts_create_table');

// Add Admin Menu
function ielts_add_admin_menu() {
    add_menu_page(
        'IELTS Exam', 
        'IELTS Exam', 
        'manage_options', 
        'ielts-exam', 
        'ielts_admin_page', 
        'dashicons-welcome-learn-more', 
        20
    );
}
add_action('admin_menu', 'ielts_add_admin_menu');

// Admin Page Content
function ielts_admin_page() {
    ?>
    <div class="wrap">
        <h1>IELTS Exam Simulator</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="ielts_save_question">
            <table class="form-table">
                <tr>
                    <th><label for="question">Question:</label></th>
                    <td><textarea name="question" required class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="option_a">Option A:</label></th>
                    <td><input type="text" name="option_a" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="option_b">Option B:</label></th>
                    <td><input type="text" name="option_b" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="option_c">Option C:</label></th>
                    <td><input type="text" name="option_c" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="option_d">Option D:</label></th>
                    <td><input type="text" name="option_d" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="correct_answer">Correct Answer:</label></th>
                    <td>
                        <select name="correct_answer" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="description">Description:</label></th>
                    <td><textarea name="description" required class="large-text"></textarea></td>
                </tr>
            </table>
            <p><input type="submit" class="button-primary" value="Save Question"></p>
        </form>
    </div>
    <?php
}

// Handle Form Submission
function ielts_save_question() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ielts_questions';

    $wpdb->insert(
        $table_name,
        [
            'question' => sanitize_text_field($_POST['question']),
            'option_a' => sanitize_text_field($_POST['option_a']),
            'option_b' => sanitize_text_field($_POST['option_b']),
            'option_c' => sanitize_text_field($_POST['option_c']),
            'option_d' => sanitize_text_field($_POST['option_d']),
            'correct_answer' => sanitize_text_field($_POST['correct_answer']),
            'description' => sanitize_textarea_field($_POST['description']),
        ]
    );

    wp_redirect(admin_url('admin.php?page=ielts-exam&success=1'));
    exit;
}
add_action('admin_post_ielts_save_question', 'ielts_save_question');
