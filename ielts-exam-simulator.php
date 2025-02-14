<?php
/**
 * Plugin Name: IELTS Exam Simulator
 * Description: A plugin to simulate the IELTS exam with MCQ questions under a common description.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Activation Hook to Create Database Table
function ielts_create_table() {
    global $wpdb;
    $description_table = $wpdb->prefix . 'ielts_descriptions';
    $questions_table = $wpdb->prefix . 'ielts_questions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql1 = "CREATE TABLE $description_table (
        id INT NOT NULL AUTO_INCREMENT,
        description TEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql2 = "CREATE TABLE $questions_table (
        id INT NOT NULL AUTO_INCREMENT,
        description_id INT NOT NULL,
        question TEXT NOT NULL,
        option_a TEXT NOT NULL,
        option_b TEXT NOT NULL,
        option_c TEXT NOT NULL,
        option_d TEXT NOT NULL,
        correct_answer VARCHAR(10) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (description_id) REFERENCES $description_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
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
    global $wpdb;
    $description_table = $wpdb->prefix . 'ielts_descriptions';
    ?>
    <div class="wrap">
        <h1>IELTS Exam Simulator - Admin Panel</h1>
        <p>Add descriptions and their related MCQs here.</p>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="ielts_save_description">
            <table class="form-table">
                <tr>
                    <th><label for="description">Passage (Description):</label></th>
                    <td><textarea name="description" required class="large-text"></textarea></td>
                </tr>
            </table>
            <p><input type="submit" class="button-primary" value="Save Passage"></p>
        </form>

        <h2>Existing Passages</h2>
        <ul>
            <?php 
            $descriptions = $wpdb->get_results("SELECT * FROM $description_table");
            foreach ($descriptions as $description) : ?>
                <li>
                    <strong><?php echo esc_html($description->description); ?></strong> 
                    <a href="admin.php?page=ielts-add-questions&desc_id=<?php echo $description->id; ?>">(Add Questions)</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

// Save Passage (Description)
function ielts_save_description() {
    global $wpdb;
    $description_table = $wpdb->prefix . 'ielts_descriptions';
    
    if (isset($_POST['description'])) {
        $wpdb->insert(
            $description_table,
            [ 'description' => sanitize_textarea_field($_POST['description']) ]
        );
    }
    wp_redirect(admin_url('admin.php?page=ielts-exam&success=1'));
    exit;
}
add_action('admin_post_ielts_save_description', 'ielts_save_description');

// Page to Add Questions
function ielts_add_questions_page() {
    global $wpdb;
    $questions_table = $wpdb->prefix . 'ielts_questions';
    $description_id = isset($_GET['desc_id']) ? intval($_GET['desc_id']) : 0;
    ?>
    <div class="wrap">
        <h1>Add Questions</h1>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="ielts_save_question">
            <input type="hidden" name="description_id" value="1">
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
            </table>
            <p><input type="submit" class="button-primary" value="Save Question"></p>
        </form>
    </div>
    <?php
}
add_action('admin_menu', function() {
    add_submenu_page('ielts-exam', 'Add Questions', 'Add Questions', 'manage_options', 'ielts-add-questions', 'ielts_add_questions_page');
});

// Save Questions
function ielts_save_question() {
    global $wpdb;
    $questions_table = $wpdb->prefix . 'ielts_questions';
    
    if (isset($_POST['description_id'], $_POST['question'], $_POST['correct_answer'])) {
        $wpdb->insert(
            $questions_table,
            [
                'description_id' => intval($_POST['description_id']),
                'question' => sanitize_text_field($_POST['question']),
                'option_a' => sanitize_text_field($_POST['option_a']),
                'option_b' => sanitize_text_field($_POST['option_b']),
                'option_c' => sanitize_text_field($_POST['option_c']),
                'option_d' => sanitize_text_field($_POST['option_d']),
                'correct_answer' => sanitize_text_field($_POST['correct_answer']),
            ]
        );
    }
    wp_redirect(admin_url('admin.php?page=ielts-add-questions&desc_id=' . $_POST['description_id'] . '&success=1'));
    exit;
}
add_action('admin_post_ielts_save_question', 'ielts_save_question');

// Shortcode for displaying IELTS passages with rotating MCQs
function ielts_display_rotating_questions() {
    global $wpdb;
    $description_table = $wpdb->prefix . 'ielts_descriptions';
    $questions_table = $wpdb->prefix . 'ielts_questions';
    $descriptions = $wpdb->get_results("SELECT * FROM $description_table");

    ob_start();
    ?>
    <div class="container mt-4">
        <?php foreach ($descriptions as $description) : ?>
            <div class="row border p-4 mb-3">
                <div class="col-md-12">
                    <h4>Passage</h4>
                    <p><?php echo esc_html($description->description); ?></p>
                </div>
                <?php 
                $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $questions_table WHERE description_id = %d", $description->id));
                if (!empty($questions)) : ?>
                    <div class="col-md-12">
                        <div class="question-container" id="question_container_<?php echo $description->id; ?>">
                            <?php foreach ($questions as $index => $question) : ?>
                                <div class="question-block question_<?php echo $description->id; ?>" style="display: <?php echo ($index === 0) ? 'block' : 'none'; ?>;">
                                    <h5><?php echo esc_html($question->question); ?></h5>
                                    <ul class="list-group">
                                        <li class="list-group-item"><input type="radio" name="answer_<?php echo $question->id; ?>" value="A"> A. <?php echo esc_html($question->option_a); ?></li>
                                        <li class="list-group-item"><input type="radio" name="answer_<?php echo $question->id; ?>" value="B"> B. <?php echo esc_html($question->option_b); ?></li>
                                        <li class="list-group-item"><input type="radio" name="answer_<?php echo $question->id; ?>" value="C"> C. <?php echo esc_html($question->option_c); ?></li>
                                        <li class="list-group-item"><input type="radio" name="answer_<?php echo $question->id; ?>" value="D"> D. <?php echo esc_html($question->option_d); ?></li>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-secondary mt-3" onclick="showNextQuestion(<?php echo $description->id; ?>)">Next Question</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function showNextQuestion(descId) {
            let questions = document.querySelectorAll('.question_' + descId);
            let currentIndex = -1;

            questions.forEach((q, index) => {
                if (q.style.display === 'block') {
                    currentIndex = index;
                    q.style.display = 'none';
                }
            });

            let nextIndex = (currentIndex + 1) % questions.length;
            questions[nextIndex].style.display = 'block';
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('ielts_questions', 'ielts_display_rotating_questions');
