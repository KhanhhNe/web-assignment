<?php

require_once 'init.php';

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'create') {
        if ($user_id == 0) {
            die('Please log in');
        }

        $course_id = $_POST['course_id'];
        $quiz_name = $_POST['quiz_name'];

        if ($course_id == '' || $quiz_name == '') {
            die('Please enter a course id and quiz name');
        }

        $result = $db->query(
            "INSERT INTO quizes (teacher_id, course_id, name) VALUES ($user_id, $course_id, '$quiz_name')"
        );
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Could not create quiz']);
            die();
        }
        echo json_encode(['success' => true, 'quiz_id' => $db->insert_id]);
        die();
    }

    if ($_POST['action'] == 'submit') {
        $quiz_id = $_POST['quiz_id'];
        $result = $db->query(
            "SELECT *
        FROM quizes
        LEFT JOIN questions ON quizes.id = questions.quiz_id
        WHERE quizes.id = $quiz_id"
        );

        if (!$result || $result->num_rows == 0) {
            die('Could not get quiz');
        }

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

        echo json_encode($result);
        die();
    }

    if ($_POST['action'] == 'update') {
        if ($user_id == 0) {
            die('Please log in');
        }

        $quiz_id = $_POST['quiz_id'];
        if ($quiz_id == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a quiz id']);
            die();
        }

        $quiz_name = $_POST['quiz_name'];
        if ($quiz_name == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a quiz name']);
            die();
        }

        $result = $db->query(
            "UPDATE quizes SET name = '$quiz_name' WHERE id = $quiz_id AND teacher_id = $user_id"
        );
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Could not update quiz']);
            die();
        }
        echo json_encode(['success' => true]);
        die();
    }

    if ($_POST['action'] == 'delete') {
        if ($user_id == 0) {
            die('Please log in');
        }

        $quiz_id = $_POST['quiz_id'];
        if ($quiz_id == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a quiz id']);
            die();
        }

        $result = $db->query(
            "DELETE FROM quizes WHERE id = $quiz_id AND teacher_id = $user_id"
        );
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Could not delete quiz']);
            die();
        }
        echo json_encode(['success' => true]);
        die();
    }
}

$quiz_id = $_GET['quiz_id'];

$result = $db->query(
    "SELECT questions.*, quizes.teacher_id
    FROM quizes
    LEFT JOIN questions ON quizes.id = questions.quiz_id
    WHERE quizes.id = $quiz_id"
);

if (!$result || $result->num_rows == 0) {
    die('Could not get quiz');
}

$quiz = fetch_assoc_all($result);
$teacher_id = ($quiz[0] ?? [])['teacher_id'] ?? 0;

?>
<form id="quiz-form">
    <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

    <?php foreach ($quiz as $index => $question) : ?>
        <?php
        if (!isset($question['id'])) {
            continue;
        }
        ?>

        <div id="q<?= $question['id'] ?>" data-difficulty="<?= $question['difficulty'] ?>" class="question card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <span>Question <?= $index + 1 ?></span>

                <button type="button" data-difficulty="easy" data-btn="success" class="difficulty-badge">Easy</button>
                <button type="button" data-difficulty="medium" data-btn="warning" class="difficulty-badge">Medium</button>
                <button type="button" data-difficulty="hard" data-btn="danger" class="difficulty-badge">Hard</button>
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
                        <label class="form-check-label" for="q<?= $question['id'] ?>a<?= $answer_index + 1 ?>"><?= $answer ?></label>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endforeach ?>

    <button type="button" class="btn btn-primary" id="submit-quiz-button">Submit Quiz</button>
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
    $('#submit-quiz-button').click(function() {
        const form = $('#quiz-form');
        const formValues = form.serializeArray();
        formValues.push({
            name: 'action',
            value: 'submit'
        });

        $.ajax({
            type: 'POST',
            url: 'quiz.php',
            data: formValues,
            success: function(response) {
                const result = JSON.parse(response);
                $('.question').removeClass('question-correct').removeClass('question-incorrect');
                $('.question label[for]').removeClass('text-success').removeClass('text-danger');

                for (const item of result) {
                    if (item.correct_answer === item.user_answer) {
                        $(`#${item.qid}`).addClass('question-correct');
                    }
                    $(`#${item.qid} label[for="${item.qid}a${item.correct_answer}"]`).addClass('text-success');
                }

                $('.question:not(.question-correct)').addClass('question-incorrect');
                $('.question label[for]:not(.text-success)').addClass('text-danger');
            }
        });
    });

    <?php if ($teacher_id == $user_id) : ?>
        $('.question').map((i, elem) => {
            const difficulty = $(elem).data('difficulty');

            for (const btn of $(elem).find('.difficulty-badge')) {
                const btnDifficulty = $(btn).data('difficulty');
                console.log({
                    btnDifficulty,
                    difficulty
                });

                if (btnDifficulty === difficulty) {
                    $(btn).addClass(`btn btn-${$(btn).data('btn')}`);
                } else {
                    $(btn).addClass(`btn btn-outline-${$(btn).data('btn')}`);
                }
            }
        })
    <?php else : ?>
    <?php endif ?>
</script>

<?php
