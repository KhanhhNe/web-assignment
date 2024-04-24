<?php

require_once 'init.php';

$user_id = $_SESSION['user_id'] ?? 0;
$result = $db->query(
    "SELECT courses.*, teachers.name AS teacher_name
    FROM courses
    INNER JOIN teachers ON courses.creator_teacher_id = teachers.id 
    ORDER BY (CASE WHEN courses.creator_teacher_id = $user_id THEN 1 ELSE 0 END) DESC"
);

if (!$result || $result->num_rows == 0) {
    die('Could not get courses');
}

$courses = fetch_assoc_all($result);

require_once 'header.php';
?>

<div class="container my-5">
    <h1 class="h1 mb-5">Dashboard</h1>

    <div class="row">
        <div id="course-list" class="col-3">
            <div id="course-list-content">
                <div class=" card mb-3">
                    <div class="card-body">
                        <input type="text" class="form-control mb-2" id="course-name" placeholder="Course Name">
                        <button class="btn btn-primary" type="button" onclick="createCourse()">Create Course</button>
                    </div>
                </div>
                <?php foreach ($courses as $course) : ?>
                    <div id="course-<?= $course['id'] ?>" class="card mb-3">
                        <div class="card-body">
                            <h4 class="course-name h4" contenteditable><?= $course['course_name'] ?></h4>
                            <p class="card-text">
                                Created by <?= $course['teacher_name'] ?><?= $course['creator_teacher_id'] == $user_id ? ' (you)' : '' ?>
                            </p>
                            <div>
                                <button class="btn btn-primary" type="button" onclick="viewCourse(<?= $course['id'] ?>)">View</button>
                                <?php if ($course['creator_teacher_id'] == $user_id) : ?>
                                    <button class="btn btn-success" type="button" onclick="updateCourse(<?= $course['id'] ?>)">Update</button>
                                    <button class="btn btn-danger" type="button" onclick="deleteCourse(<?= $course['id'] ?>)">Delete</button>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
        <div id="course-container" class="col-9"></div>
    </div>
</div>

<script>
    function viewCourse(course_id) {
        $('#course-container').load(`course.php?course_id=${course_id}`);
    }

    function createCourse() {
        const course_name = $('#course-name').val();

        $.post('course.php', {
            action: 'create',
            course_name: course_name
        }, function(data) {
            const result = JSON.parse(data);
            console.log(result);
            if (result.success) {
                $('#course-list').load(
                    'dashboard.php #course-list-content', undefined,
                    () => showRedDot($(`#course-${result.course_id}`))
                );
            } else {
                alert(result.error);
            }
        });
    }

    function updateCourse(course_id) {
        const course_name = $(`#course-${course_id} .course-name`).text();

        $.post('course.php', {
            action: 'update',
            course_id: course_id,
            course_name: course_name
        }, function(data) {
            const result = JSON.parse(data);
            console.log(result);
            if (result.success) {
                $('#course-list').load(
                    'dashboard.php #course-list-content', undefined,
                    () => {
                        $(`#course-${course_id}`).find('h4').text(course_name);
                        showRedDot($(`#course-${course_id}`));
                    });
            } else {
                alert(result.error);
            }
        });
    }

    function deleteCourse(course_id) {
        if (confirm('Are you sure you want to delete this course?')) {
            $.post('course.php', {
                action: 'delete',
                course_id: course_id
            }, function(data) {
                const result = JSON.parse(data);
                if (result.success) {
                    $('#course-list').load('dashboard.php #course-list-content');
                } else {
                    alert(result.error);
                }
            });
        }
    }
</script>

<?php
require_once 'footer.php';
