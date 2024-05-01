<?php

require_once 'init.php';

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'create') {
        require_login();
        [$course_id, $quiz_name] = required_keys(['course_id', 'quiz_name']);

        $db->query(
            "INSERT INTO quizes (teacher_id, course_id, name) VALUES ($user_id, $course_id, '$quiz_name')"
        ) || die_json(['success' => false, 'error' => 'Could not create quiz']);

        die_json(['success' => true, 'quiz_id' => $db->insert_id]);
    }

    if ($_POST['action'] == 'submit') {
        [$quiz_id] = required_keys(['quiz_id']);

        $result = $db->query(
            "SELECT *
            FROM quizes
            LEFT JOIN questions ON quizes.id = questions.quiz_id
            WHERE quizes.id = $quiz_id"
        );
        !$result && die_json(['success' => false, 'error' => 'Could not get quiz']);

        $quiz = fetch_assoc_all($result);
        $result = [];

        foreach ($_POST as $key => $value) {
            if (preg_match('/^q\d+$/', $key)) {
                $question_id = intval(substr($key, 1));
                $answer = $value;

                foreach ($quiz as $question) {
                    if ($question['id'] == $question_id) {
                        break;
                    }
                }

                $result[] = [
                    'qid' => $key,
                    'question_id' => $question_id,
                    'user_answer' => $answer,
                    'correct_answer' => isset($question['correct_answer']) ? $question['correct_answer'] : null
                ];
            }
        }

        die_json($result);
    }

    if ($_POST['action'] == 'update') {
        require_login();
        [$quiz_id, $quiz_name] = required_keys(['quiz_id', 'quiz_name']);

        $db->query(
            "UPDATE quizes SET name = '$quiz_name' WHERE id = $quiz_id AND teacher_id = $user_id"
        ) || die_json(['success' => false, 'error' => 'Could not update quiz']);

        die_json(['success' => true]);
    }

    if ($_POST['action'] == 'delete') {
        require_login();
        [$quiz_id] = required_keys(['quiz_id']);

        $db->query(
            "DELETE FROM quizes WHERE id = $quiz_id AND teacher_id = $user_id"
        ) || die_json(['success' => false, 'error' => 'Could not delete quiz']);

        die_json(['success' => true]);
    }

    if ($_POST['action'] == 'update question') {
        require_login();
        [$quiz_id, $question_id] = required_keys(['quiz_id', 'question_id']);

        foreach (['difficulty', 'question', 'answers'] as $key) {
            $value = $_POST[$key] ?? '';
            if ($value == '') {
                continue;
            }

            $db->query(
                "UPDATE questions SET $key = '$value' WHERE quiz_id = $quiz_id AND id = $question_id"
            ) || die_json(['success' => false, 'error' => "Could not update question $key"]);
        }

        die_json(['success' => true]);
    }

    die_json(['success' => false, 'error' => 'Unknown action']);
}

$quiz_id = $_GET['quiz_id'];

$result = $db->query(
    "SELECT questions.*, quizes.teacher_id
    FROM quizes
    LEFT JOIN questions ON quizes.id = questions.quiz_id
    WHERE quizes.id = $quiz_id"
);
!$result && die('Could not get quiz');

$quiz = fetch_assoc_all($result);
$teacher_id = ($quiz[0] ?? [])['teacher_id'] ?? 0;

function difficultyButtons($question)
{
    global $user_id, $teacher_id;

    ob_start();
    $colors = [
        'easy' => 'success',
        'medium' => 'warning',
        'hard' => 'danger'
    ];

    if ($user_id == $teacher_id) {
        foreach (['easy', 'medium', 'hard'] as $difficulty) {
            $color = $colors[$difficulty];
            $prefix = $difficulty == $question['difficulty'] ? 'btn-' : 'btn-outline-';
            $class = "btn $prefix$color";

?>
            <button type="button" class="difficulty-badge <?= $class ?>" onclick="updateQuestionDifficulty(<?= $question['id'] ?>, '<?= $difficulty ?>')">
                <?= ucfirst($difficulty) ?>
            </button>
        <?php
        }
    } else {
        $color = $colors[$question['difficulty']];
        $class = "btn btn-$color";
        ?>
        <button type="button" class="difficulty-badge <?= $class ?>">
            <?= ucfirst($question['difficulty']) ?>
        </button>
<?php
    }

    return ob_get_clean();
}

?>
<form id="quiz-form">
    <div id="quiz-form-content">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

        <?php foreach ($quiz as $index => $question) : ?>
            <?php
            if (!isset($question['id'])) {
                continue;
            }
            ?>

            <div id="q<?= $question['id'] ?>" data-id="<?= $question['id'] ?>" data-difficulty="<?= $question['difficulty'] ?>" class="question card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <span>Question <?= $index + 1 ?></span>

                    <?= difficultyButtons($question) ?>
                </div>
                <div class="card-body">
                    <?php if (isset($question['image_url'])) : ?>
                        <img src="<?= $question['image_url'] ?>" class="img">
                    <?php endif ?>
                    <p><?= $question['question'] ?></p>
                    <input type="hidden" name="q<?= $question['id'] ?>" value="0" checked />
                    <?php foreach (json_decode($question['answers'] ?? "['', '', '', '']") as $answer_index => $answer) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?= $question['id'] ?>" id="q<?= $question['id'] ?>a<?= $answer_index + 1 ?>" value="<?= $answer_index + 1 ?>">
                            <label class="answer-label form-check-label" for="q<?= $question['id'] ?>a<?= $answer_index + 1 ?>"><?= $answer ?></label>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endforeach ?>

        <button type="button" onclick="submitQuiz()" class="btn btn-primary">Submit Quiz</button>
    </div>
</form>

<style>
    .question.question-correct .card-header {
        background-color: var(--bs-success);
        color: var(--bs-white);
    }

    .question.question-incorrect .card-header {
        background-color: var(--bs-danger);
        color: var(--bs-white);
    }
</style>

<script>
    (() => {
        const teacherId = <?= $teacher_id ?>;
        const userId = <?= $user_id ?>;
        const quizId = <?= $quiz_id ?>;

        function submitQuiz() {
            const form = $('#quiz-form');
            const formValues = form.serializeArray();
            formValues.push({
                name: 'action',
                value: 'submit'
            });

            $.post('quiz.php', formValues, function(response) {
                const result = (response);
                $('.question').removeClass(['question-correct', 'question-incorrect']);
                $('.question .answer-label').removeClass(['text-success', 'text-danger']);

                for (const item of result) {
                    if (item.correct_answer === item.user_answer) {
                        $(`#${item.qid}`).addClass('question-correct');
                    }
                    $(`#${item.qid} .answer-label[for="${item.qid}a${item.correct_answer}"]`).addClass('text-success');
                }

                $('.question:not(.question-correct)').addClass('question-incorrect');
                $('.question .answer-label:not(.text-success)').addClass('text-danger');
            });
        };

        function reloadQuestions() {
            $('#quiz-form').load(`quiz.php?quiz_id=${quizId} #quiz-form-content`);
        }

        function updateQuestionDifficulty(questionId, difficulty) {
            $.post('quiz.php', {
                action: 'update question',
                quiz_id: quizId,
                question_id: questionId,
                difficulty: difficulty
            }, () => reloadQuestions());
        }

        Object.assign(window, {
            updateQuestionDifficulty,
            submitQuiz
        });
    })()
</script>

<?php
