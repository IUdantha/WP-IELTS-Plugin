<?php
function ielts_mcq_quiz_shortcode() {
    global $wpdb;
    $description_table = $wpdb->prefix . 'ielts_descriptions';
    $questions_table = $wpdb->prefix . 'ielts_questions';
    $results_table = $wpdb->prefix . 'ielts_results';
    $user_id = get_current_user_id();
    $description_id = 1; // Fetch description with ID = 1

    // Fetch description
    $description = $wpdb->get_var(
        $wpdb->prepare("SELECT description FROM $description_table WHERE id = %d", $description_id)
    );

    // Fetch all questions under description_id = 1
    $questions = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $questions_table WHERE description_id = %d", $description_id)
    );

    if (empty($questions)) {
        return "<p>No questions available.</p>";
    }

    ob_start(); // Start output buffering
    ?>

    <div class="quiz-container">
        <!-- Display Description -->
        <div class="quiz-description">
            <h2>Reading Passage</h2>
            <p><?php echo esc_html($description); ?></p>
        </div>

        <!-- Display MCQs -->
        <form method="post">
            <?php wp_nonce_field('ielts_quiz_submit', 'ielts_quiz_nonce'); ?>
            <input type="hidden" name="quiz_submitted" value="1">

            <?php foreach ($questions as $index => $question) : ?>
                <div class="question">
                    <p><strong><?php echo ($index + 1) . ". " . esc_html($question->question); ?></strong></p>
                    <label><input type="radio" name="answers[<?php echo $question->id; ?>]" value="A" required> <?php echo esc_html($question->option_a); ?></label><br>
                    <label><input type="radio" name="answers[<?php echo $question->id; ?>]" value="B"> <?php echo esc_html($question->option_b); ?></label><br>
                    <label><input type="radio" name="answers[<?php echo $question->id; ?>]" value="C"> <?php echo esc_html($question->option_c); ?></label><br>
                    <label><input type="radio" name="answers[<?php echo $question->id; ?>]" value="D"> <?php echo esc_html($question->option_d); ?></label><br>
                </div>
                <hr>
            <?php endforeach; ?>

            <button type="submit">Submit</button>
        </form>

        <?php
        // Handle Form Submission
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quiz_submitted'])) {
            if (!isset($_POST['ielts_quiz_nonce']) || !wp_verify_nonce($_POST['ielts_quiz_nonce'], 'ielts_quiz_submit')) {
                return "<p>Security check failed. Please try again.</p>";
            }

            $answers = $_POST['answers'] ?? [];
            $total_questions = count($questions);
            $score = 0;

            // Check answers
            foreach ($questions as $question) {
                if (isset($answers[$question->id]) && $answers[$question->id] === $question->correct_answer) {
                    $score++;
                }
            }

            // Save results in database
            $wpdb->insert($results_table, [
                'user_id' => $user_id,
                'score' => $score,
                'total_questions' => $total_questions,
                'test_date' => current_time('mysql')
            ]);

            echo "<p>Your score: <strong>$score / $total_questions</strong></p>";
        }
        ?>

    </div>

    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('ielts_questions', 'ielts_mcq_quiz_shortcode');
