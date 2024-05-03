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

$course_id = $_GET['id'];
$quiz_id = $_GET['quiz_id'] ?? 0;

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

function quiz_url($quiz_id)
{
    global $course_id;
    return "course.php?id={$course_id}&quiz_id={$quiz_id}";
}

if ($quiz_id) {
    $result = $db->query(
        "SELECT questions.*, quizes.teacher_id
        FROM quizes
        LEFT JOIN questions ON quizes.id = questions.quiz_id
        WHERE quizes.id = $quiz_id"
    );
    !$result && die('Could not get quiz');

    $questions = fetch_assoc_all($result);
} else {
    $questions = [];
}
$teacher_id = ($questions[0] ?? [])['teacher_id'] ?? 0;

if (isset($_GET['api'])) {
    die_json(['success' => true, 'questions' => $questions]);
}

require_once 'header.php';

?>

<div class="container my-5">
    <div class="row">
        <div id="quiz-list" class="col-3 vstack gap-2">
            <?php foreach ($course as $index => $quiz) : ?>
                <div id="quiz-<?= $quiz['id'] ?>" class="quiz card mb-3 <?= $quiz_id == $quiz['id'] ? 'tw-ring' : '' ?>">
                    <div class="card-body">
                        <a href="<?= quiz_url($quiz['id']) ?>" class="fs-4 tw-underline d-block w-100 mb-3"><?= $index + 1 ?>. <?= $quiz['name'] ?></a>
                        <div class="card-text hstack justify-content-between">
                            <span class="text-muted fst-italic">by <?= $quiz['teacher_id'] == $user_id ? 'you' : $quiz['teacher_name'] ?></span>
                            <?php if ($quiz['teacher_id'] == $user_id) : ?>
                                <div class="hstack gap-1">
                                    <button class="btn btn-outline-primary btn-sm tw-aspect-square" type="button" onclick="updateQuiz(<?= $quiz['id'] ?>, '<?= $quiz['name'] ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm tw-aspect-square" type="button" onclick="deleteQuiz(<?= $quiz['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
            <?php if ($user_id) : ?>
                <button type="button" class="btn btn-primary" onclick="addQuiz()">
                    <i class="bi bi-plus fs-3"></i>
                </button>
            <?php endif ?>
        </div>
        <div id="quiz-container" class="col-9">
            <?php if ($quiz_id) : ?>
                <form id="quiz-form" onsubmit="return false;" class="vstack gap-5">
                    <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

                    <div class="quiz-content vstack gap-2"></div>

                    <?php if ($quiz['teacher_id'] == $user_id) : ?>
                        <button type="button" class="btn btn-primary tw-ml-auto" onclick="updateQuestions()">Update</button>
                    <?php elseif (!$user_id) : ?>
                        <button id="submit-button" type="button" class="btn btn-primary tw-ml-auto" onclick="submitQuiz()">Submit</button>
                    <?php endif ?>
                </form>
            <?php endif ?>
        </div>
    </div>
</div>

<script>
    let data = null;
    const userId = <?= $user_id ?>;

    function getQuestion(id) {
        return data.quizes?.find(question => question.id == id);
    }

    function reloadQuizData() {
        return new Promise(resolve => {
            $.get(`${window.location.href}&api=1`, function(result) {
                data = result;
                data.questions = data.questions.filter(question => question.id);
                data.teacherId = (data.questions[0] ?? {}).teacher_id;
                resolve();
            });
        });
    }
    reloadQuizData();

    function showQuestion(question = {}, index) {
        const isTeacher = !!userId;
        const editable = question.teacher_id == userId;
        const disabled = question.teacher_id != userId ? 'disabled' : '';

        $(`#quiz-form .quiz-content`).append(`
            <div id="question-${question.id}" class="card">
                <div class="card-body vstack gap-3">
                    <div class="card-subtitle hstack align-items-center gap-3">
                        <span class="text-muted fst-italic">Question ${index + 1}</span>
                        <div class="hstack gap-1">
                            ${difficultyButtons(question)}
                        </div>
                        ${editable ? `
                            <button class="btn btn-outline-danger btn-sm tw-aspect-square tw-ml-auto" type="button" onclick="deleteQuestion(${question.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                    ${isTeacher ? `
                        <div class="card-title">
                            <input type="text" class="form-control" name="question-${question.id}-content" value="${question.question}" placeholder="Question" ${disabled}>
                        </div>
                        <div class="card-text">
                            <div class="question-image w-100 tw-h-[10rem]">
                                <input type="hidden" name="question-${question.id}-image_url" value="${question.image_url}">
                                ${updatableImage(question.image_url || noImageUrl, `updateQuestionImage(${question.id})`, editable)}
                            </div>
                        </div>
                        <div class="card-text">
                            ${JSON.parse(question.answers).map((answer, index) => `
                                <div class="form-check hstack gap-2">
                                    <input class="form-check-input tw-my-auto" type="radio" name="question-${question.id}-correct_answer" value="${index}" ${index == question.correct_answer ? 'checked' : ''} ${disabled}>
                                    <input type="text" class="form-control" name="question-${question.id}-answer-${index}" value="${answer}" placeholder="Answer ${index + 1}" ${disabled}>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div class="card-title">
                            ${question.question}
                        </div>
                        ${question.image_url ? `
                            <div class="card-text">
                                <div class="question-image w-100 tw-h-[10rem]">
                                    ${updatableImage(question.image_url || noImageUrl, '', editable)}
                                </div>
                            </div>
                        ` : ''}
                        <div class="card-text"> 
                            ${JSON.parse(question.answers).map((answer, index) => `
                                <div class="form-check hstack gap-2">
                                    <input class="form-check-input tw-my-auto" type="radio" name="question-${question.id}-answer" id="question-${question.id}-answer-${index}" value="${index}">
                                    <label class="form-check-label" for="question-${question.id}-answer-${index}">
                                        ${answer}
                                    </label>
                                </div>
                            `).join('')}
                        </div>
                    `}
                </div>
            </div>
        `);
    }

    function showQuestions() {
        $('#quiz-form .quiz-content').empty();
        let index = 0;
        for (const question of data.questions) {
            showQuestion(question, index++);
        }

        if (userId) {
            $('#quiz-form .quiz-content').append(`
                <button type="button" class="btn btn-primary" onclick="addQuestion()">
                    <i class="bi bi-plus fs-3"></i>
                </button>
        `)
        }
    }

    setTimeout(function autoShowQuestions() {
        if (data !== null) {
            showQuestions();
        } else {
            setTimeout(autoShowQuestions);
        }
    })

    async function updateQuestionImage(question_id) {
        const {
            success,
            values
        } = await promptModal({
            image_url: {
                title: 'Question image URL',
                defaultValue: getQuestion(question_id)?.image_url
            }
        });

        if (success && values.image_url) {
            const image = values.image_url;
            $(`#question-${question_id}-image_url`).val(image);
            $(`#question-${question_id} .question-image img`).attr('src', image);
        }
    }

    function difficultyButtons(question) {
        const colors = {
            easy: 'success',
            medium: 'warning',
            hard: 'danger'
        }
        const ucfirst = str => str.charAt(0).toUpperCase() + str.slice(1);

        if (userId == data.teacherId) {
            return ['easy', 'medium', 'hard'].map(difficulty => {
                const color = colors[difficulty];
                const inputName = `question-${question.id}-difficulty`;
                const inputId = `question-${question.id}-${difficulty}`;

                return `
                    <input type="radio" class="btn-check" name="${inputName}" id="${inputId}" ${difficulty == question.difficulty ? 'checked' : ''} value="${difficulty}">
                    <label class="btn btn-sm rounded-2 btn-outline-${color}" for="${inputId}">
                        ${ucfirst(difficulty)}
                    </label>
                `;
            }).join('');
        } else {
            const color = colors[question.difficulty];
            const inputId = `question-${question.id}-difficulty`;

            return `
                <label class="btn btn-sm rounded-2 btn-${color}">
                    ${ucfirst(question.difficulty)}
                </label>
            `;
        }
    }

    function updateQuestions() {
        const values = getFormData($(`#quiz-form`));

        $.post('quiz.php', {
            action: 'update questions',
            quiz_id: data.id,
            ...values
        }, function(data) {
            if (data.success) {
                reloadQuizData().then(() => {
                    showQuestions();
                    showToast('Quiz updated', {
                        type: 'success'
                    });
                });
            }
        });
    }

    function addQuestion() {
        $.post('quiz.php', {
            action: 'add question',
            quiz_id: <?= $quiz_id ?>
        }, function(data) {
            if (data.success) {
                reloadQuizData().then(() => {
                    showQuestions();
                });
            }
        });
    }

    function deleteQuestion(question_id) {
        if (!confirm('Are you sure you want to delete this question?')) {
            return;
        }

        $.post('quiz.php', {
            action: 'delete question',
            quiz_id: <?= $quiz_id ?>,
            question_id: question_id
        }, function(data) {
            if (data.success) {
                reloadQuizData().then(() => {
                    showQuestions();
                    showToast('Question deleted', {
                        type: 'success'
                    });
                });
            }
        });
    }

    function reloadQuizList(highlight_id = null) {
        $('#quiz-list').load(`course.php?id=${<?= $course_id ?>} #quiz-list > *`, undefined, () => {
            if (highlight_id) {
                showRedDot($(`#quiz-${highlight_id}`));
            }
        });
    }

    async function addQuiz() {
        const {
            success,
            values
        } = await promptModal({
            quiz_name: {
                title: 'Quiz name',
            },
        });

        if (success) {
            $.post('quiz.php', {
                action: 'create',
                course_id: <?= $course_id ?>,
                quiz_name: values.quiz_name
            }, function(data) {
                if (data.success) {
                    showToast('Quiz created', {
                        type: 'success'
                    });
                    reloadQuizList(data.quiz_id);
                }
            });
        }
    }

    async function updateQuiz(quiz_id, quiz_name) {
        const {
            success,
            values
        } = await promptModal({
            quiz_name: {
                title: 'Quiz name',
                defaultValue: quiz_name
            },
        });

        if (success && values.quiz_name) {
            $.post('quiz.php', {
                action: 'update',
                quiz_id: quiz_id,
                quiz_name: values.quiz_name
            }, function(data) {
                if (data.success) {
                    showToast('Quiz updated', {
                        type: 'success'
                    });
                    reloadQuizList();
                }
            });
        }
    }

    function deleteQuiz(quiz_id) {
        if (!confirm('Are you sure you want to delete this quiz?')) {
            return;
        }

        $.post('quiz.php', {
            action: 'delete',
            quiz_id: quiz_id
        }, function(data) {
            if (data.success) {
                reloadQuizList();
                showToast('Quiz deleted', {
                    type: 'success'
                });
            }
        });
    }

    function submitQuiz() {
        const formValues = getFormData($(`#quiz-form`));
        const answered = Object.keys(formValues).filter(key => key.startsWith('question-'));
        const totalQuestions = data.questions.length;

        if (answered.length != totalQuestions) {
            if (!confirm(`You answered ${answered.length} of ${totalQuestions} questions. Are you sure you want to submit?`)) {
                return;
            }
        }

        if (!confirm('Are you sure you want to submit this quiz?')) {
            return;
        }

        $('#submit-button').prop('disabled', true);

        let score = 0;
        const totalScore = totalQuestions;

        const getQuestionElem = question_id => $(`#question-${question_id} .card-subtitle`);
        const getLabel = (question_id, answer) => $(`label[for="question-${question_id}-answer-${answer}"]`);

        for (const question of data.questions) {
            const answer = formValues[`question-${question.id}-answer`] ?? '';
            if (question.correct_answer == answer) {
                score += 1;
                getQuestionElem(question.id).append(`<i class="bi bi-check-circle-fill text-success"></i>`);
            } else {
                getQuestionElem(question.id).append(`<i class="bi bi-x-circle-fill text-danger"></i>`);

                getLabel(question.id, answer).addClass('text-danger');
                getLabel(question.id, answer).append(`<i class="bi bi-x-circle-fill"></i>`);
            }
            getLabel(question.id, question.correct_answer).addClass('text-success');
            getLabel(question.id, question.correct_answer).append(`<i class="bi bi-check-circle-fill"></i>`);
        }

        $(`#quiz-form .quiz-content`).after(`
            <div id="test-summary" class="text-success">
                You got ${score} out of ${totalScore} questions correct.
            </div>
        `);
    }
</script>

<?php
require_once 'footer.php';
