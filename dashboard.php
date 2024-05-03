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

if (isset($_GET['api'])) {
    die_json(['success' => true, 'courses' => $courses]);
}

require_once 'header.php';
?>

<div class="container my-5">
    <?php if ($user_id) : ?>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" type="button" onclick="createCourse()">Create</button>
        </div>
    <?php endif ?>

    <div id="course-container" class="tw-grid tw-grid-cols-3 xl:tw-grid-cols-4 gap-4">
        <?php foreach ($courses as $course) : ?>
            <div id="course-<?= $course['id'] ?>" class="card w-100">
                <div class="card-img-top overflow-hidden w-100 tw-aspect-video">
                    <?= updatableImage(
                        $course['course_img'] ? $course['course_img'] : $no_image_url,
                        "updateCourseImage({$course['id']})",
                        $course['creator_teacher_id'] == $user_id
                    ) ?>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="card-title">
                        <a href="course.php?id=<?= $course['id'] ?>" class="fs-4 tw-underline">
                            <?= $course['course_name'] ?>
                        </a>
                    </div>
                    <div class="card-text hstack justify-content-between">
                        <span class="text-muted fst-italic">
                            by <?= $course['creator_teacher_id'] == $user_id ? 'you' : $course['teacher_name'] ?>
                        </span>
                        <?php if ($course['creator_teacher_id'] == $user_id) : ?>
                            <div class="hstack gap-1">
                                <button class="btn btn-outline-primary btn-sm tw-aspect-square" type="button" onclick="updateCourse(<?= $course['id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm tw-aspect-square" type="button" onclick="deleteCourse(<?= $course['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>

<script>
    let courses;

    function getCourse(id) {
        return courses?.find(course => course.id == id);
    }

    function reloadCourses() {
        $.get('dashboard.php?api=1', function(data) {
            courses = data.courses;
        });
    }
    reloadCourses();

    async function updateCourseImage(course_id) {
        const input = await promptModal({
            course_img: {
                title: 'New course cover image',
                defaultValue: getCourse(course_id)?.course_img
            },
        });
        if (input.success) {
            $.post('course.php', {
                action: 'update',
                course_id,
                ...input.values,
            }, function(data) {
                if (data.success) {
                    showToast('Course image updated!', {
                        type: 'success'
                    });
                    $('#course-container').load('dashboard.php #course-container > *');
                    reloadCourses();
                }
            });
        }
    }

    async function createCourse() {
        const input = await promptModal({
            course_name: {
                title: 'Course name'
            },
            course_img: {
                title: 'Course cover image'
            }
        });
        if (input.success) {
            $.post('course.php', {
                action: 'create',
                ...input.values,
            }, function(data) {
                if (data.success) {
                    $('#course-container').load(
                        'dashboard.php #course-container > *', undefined,
                        () => showRedDot($(`#course-${data.course_id}`))
                    );
                    showToast('Course created!', {
                        type: 'success'
                    });
                    reloadCourses();
                }
            });
        }
    }

    async function updateCourse(course_id) {
        const course = getCourse(course_id);

        const input = await promptModal({
            course_name: {
                title: 'Course name',
                defaultValue: course?.course_name
            },
            course_img: {
                title: 'Course cover image',
                defaultValue: course?.course_img
            }
        });
        if (input.success) {
            $.post('course.php', {
                action: 'update',
                course_id,
                ...input.values,
            }, function(data) {
                if (data.success) {
                    $('#course-container').load(
                        'dashboard.php #course-container > *', undefined,
                        () => showRedDot($(`#course-${course_id}`))
                    );
                    showToast('Course updated!', {
                        type: 'success'
                    });
                    reloadCourses();
                }
            });
        }
    }

    function deleteCourse(course_id) {
        if (confirm('Are you sure you want to delete this course?')) {
            $.post('course.php', {
                action: 'delete',
                course_id: course_id
            }, function(data) {
                if (data.success) {
                    $('#course-container').load('dashboard.php #course-container > *');
                    reloadCourses();
                }
            });
        }
    }
</script>

<?php

require_once 'footer.php';
