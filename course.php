<?php

require_once 'init.php';

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'create') {
        if ($user_id == 0) {
            echo json_encode(['success' => false, 'error' => 'Please log in']);
            die();
        }

        $course_name = $_POST['course_name'];
        if ($course_name == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a course name']);
            die();
        }

        $result = $db->query(
            "INSERT INTO courses (creator_teacher_id, course_name) VALUES ($user_id, '$course_name')"
        );
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Could not create course']);
            die();
        }
        echo json_encode(['success' => true, 'course_id' => $db->insert_id]);
        die();
    }

    if ($_POST['action'] == 'update') {
        if ($user_id == 0) {
            echo json_encode(['success' => false, 'error' => 'Please log in']);
            die();
        }

        $course_id = $_POST['course_id'];
        if ($course_id == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a course id']);
            die();
        }

        $course_name = $_POST['course_name'];
        if ($course_name == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a course name']);
            die();
        }

        $result = $db->query(
            "UPDATE courses SET course_name = '$course_name' WHERE id = $course_id AND creator_teacher_id = $user_id"
        );
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Could not update course']);
            die();
        }
        echo json_encode(['success' => true]);
        die();
    }

    if ($_POST['action'] == 'delete') {
        if ($user_id == 0) {
            echo json_encode(['success' => false, 'error' => 'Please log in']);
            die();
        }

        $course_id = $_POST['course_id'];
        if ($course_id == '') {
            echo json_encode(['success' => false, 'error' => 'Please enter a course id']);
            die();
        }

        $result = $db->query(
            "DELETE FROM courses WHERE id = $course_id AND creator_teacher_id = $user_id"
        );
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Could not delete course']);
            die();
        }
        echo json_encode(['success' => true]);
        die();
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    die();
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
            const result = JSON.parse(data);
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
            const result = JSON.parse(data);
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
                const result = JSON.parse(data);
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
