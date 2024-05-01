<?php

require_once 'init.php';

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'create') {
        require_login();
        [$course_name, $course_img] = required_keys(['course_name', 'course_img']);

        $db->query(
            "INSERT INTO courses (creator_teacher_id, course_name, course_img) VALUES ($user_id, '$course_name', '$course_img')"
        ) || die_json(['success' => false, 'error' => 'Could not create course']);

        die_json(['success' => true, 'course_id' => $db->insert_id]);
    }

    if ($_POST['action'] == 'update') {
        require_login();
        [$course_id] = required_keys(['course_id']);
        [$course_name, $course_img] = required_at_least_one_key(['course_name', 'course_img']);

        if ($course_name) {
            $db->query(
                "UPDATE courses SET course_name = '$course_name' WHERE id = $course_id AND creator_teacher_id = $user_id"
            ) || die_json(['success' => false, 'error' => 'Could not update course']);
        }

        if ($course_img) {
            $db->query(
                "UPDATE courses SET course_img = '$course_img' WHERE id = $course_id AND creator_teacher_id = $user_id"
            ) || die_json(['success' => false, 'error' => 'Could not update course']);
        }

        die_json(['success' => true]);
    }

    if ($_POST['action'] == 'delete') {
        require_login();
        [$course_id] = required_keys(['course_id']);

        $db->query(
            "DELETE FROM courses WHERE id = $course_id AND creator_teacher_id = $user_id"
        ) || die_json(['success' => false, 'error' => 'Could not delete course']);

        die_json(['success' => true]);
    }

    die_json(['success' => false, 'error' => 'Unknown action']);
}

$course_id = $_GET['course_id'];

$result = $db->query(
    "SELECT quizes.*, teachers.name AS teacher_name
    FROM courses
    LEFT JOIN quizes ON courses.id = quizes.course_id
    INNER JOIN teachers ON quizes.teacher_id = teachers.id
    WHERE courses.id = $course_id"
);

if (!$result || $result->num_rows == 0) {
    die('Could not get course');
}

$course = fetch_assoc_all($result);

?>

<div class="row">
    <div id="quiz-list" class="col-4">
        <div id="quiz-list-content">
            <div class="card mb-3">
                <div class="card-body">
                    <input type="text" class="form-control mb-2" id="quiz-name" placeholder="Quiz Name">
                    <button class="btn btn-primary" type="button" onclick="createQuiz()">Create Quiz</button>
                </div>
            </div>
            <?php foreach ($course as $index => $quiz) : ?>
                <div id="quiz-<?= $quiz['id'] ?>" class="card mb-3">
                    <div class="card-body">
                        <h4 class="quiz-name h4" contenteditable><?= $quiz['name'] ?></h4>
                        <p class="card-text">
                            Created by <?= $quiz['teacher_name'] ?><?= $quiz['teacher_id'] == $user_id ? ' (you)' : '' ?>
                        </p>
                        <button class="btn btn-primary" type="button" onclick="viewQuiz(<?= $quiz['id'] ?>)">View</button>
                        <?php if ($quiz['teacher_id'] == $user_id) : ?>
                            <button class="btn btn-success" type="button" onclick="updateQuiz(<?= $quiz['id'] ?>)">Update</button>
                            <button class="btn btn-danger" type="button" onclick="deleteQuiz(<?= $quiz['id'] ?>)">Delete</button>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    <div id="quiz-container" class="col-8"></div>
</div>

<script>
    function viewQuiz(quiz_id) {
        $('#quiz-container').load(`quiz.php?quiz_id=${quiz_id}`);
    }

    function createQuiz() {
        const quiz_name = $('#quiz-name').val();

        $.post('quiz.php', {
            action: 'create',
            course_id: <?= $course_id ?>,
            quiz_name: quiz_name
        }, function(data) {
            const result = data;
            console.log(result);
            if (result.success) {
                $('#quiz-list').load(
                    `course.php?course_id=${<?= $course_id ?>} #quiz-list-content`, undefined,
                    () => showRedDot($(`#quiz-${result.quiz_id}`))
                );
            } else {
                alert(result.error);
            }
        });
    }

    function updateQuiz(quiz_id) {
        const quiz_name = $(`#quiz-${quiz_id} .quiz-name`).text();

        $.post('quiz.php', {
            action: 'update',
            quiz_id: quiz_id,
            quiz_name: quiz_name
        }, function(data) {
            const result = data;
            console.log(result);
            if (result.success) {
                $('#quiz-list').load(
                    `course.php?course_id=${<?= $course_id ?>} #quiz-list-content`, undefined,
                    () => {
                        $(`#quiz-${quiz_id}`).find('h4').text(quiz_name);
                        showRedDot($(`#quiz-${quiz_id}`));
                    });
            } else {
                alert(result.error);
            }
        });
    }

    function deleteQuiz(quiz_id) {
        if (confirm('Are you sure you want to delete this quiz?')) {
            $.post('quiz.php', {
                action: 'delete',
                quiz_id: quiz_id
            }, function(data) {
                const result = data;
                if (result.success) {
                    $('#quiz-list').load(`course.php?course_id=${<?= $course_id ?>} #quiz-list-content`);
                } else {
                    alert(result.error);
                }
            });
        }
    }
</script>

<?php
require_once 'footer.php';
