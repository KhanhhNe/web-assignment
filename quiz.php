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

    if ($_POST['action'] == 'update questions') {
        require_login();

        [$quiz_id] = required_keys(['quiz_id']);

        $data = [];
        $sorted_post_keys = array_keys(array_filter($_POST));
        sort($sorted_post_keys);

        foreach ($sorted_post_keys as $key) {
            $value = $_POST[$key] ?? '';

            $args = explode('-', $key);
            if ($args[0] != 'question') {
                continue;
            }

            $question_id = intval($args[1]);
            $data[$question_id] = $data[$question_id] ?? [
                'id' => $question_id,
                'difficulty' => 'easy',
                'question' => '',
                'image_url' => '',
                'answers' => ['', '', '', ''],
                'correct_answer' => 0
            ];

            switch ($args[2]) {
                case 'difficulty':
                    $data[$question_id]['difficulty'] = $value;
                    break;
                case 'content':
                    $data[$question_id]['question'] = $value;
                    break;
                case 'image_url':
                    $data[$question_id]['image_url'] = $value != null && $value != 'null' ? $value : '';
                    break;
                case 'answer':
                    $ind = intval($args[3]);
                    $data[$question_id]['answers'][$ind] = $value;
                    break;
                case 'correct_answer':
                    $data[$question_id]['correct_answer'] = intval($value);
                    break;
            }
        }

        foreach ($data as $question_id => $question) {
            $question['answers'] = json_encode($question['answers']);

            $db->query(
                "UPDATE questions
                SET
                    difficulty = '{$question['difficulty']}',
                    question = '{$question['question']}',
                    image_url = '{$question['image_url']}',
                    answers = '{$question['answers']}',
                    correct_answer = '{$question['correct_answer']}'
                WHERE id = $question_id"
            ) || die_json(['success' => false, 'error' => "Could not update question $question_id. Reason: $db->error"]);
        }

        die_json(['success' => true]);
    }

    if ($_POST['action'] == 'add question') {
        require_login();
        [$quiz_id] = required_keys(['quiz_id']);

        $db->query(
            "INSERT INTO questions (quiz_id, difficulty, question, image_url, answers, correct_answer) VALUES ($quiz_id, 'easy', '', '', '[\"\", \"\", \"\", \"\"]', 0)"
        ) || die_json(['success' => false, 'error' => "Could not add question. Reason: $db->error"]);

        die_json(['success' => true]);
    }

    if ($_POST['action'] == 'delete question') {
        require_login();
        [$quiz_id, $question_id] = required_keys(['quiz_id', 'question_id']);

        $db->query(
            "DELETE FROM questions WHERE quiz_id = $quiz_id AND id = $question_id"
        ) || die_json(['success' => false, 'error' => "Could not delete question. Reason: $db->error"]);

        die_json(['success' => true]);
    }

    die_json(['success' => false, 'error' => 'Unknown action']);
}

die_json(['success' => false, 'error' => 'Unknown action']);
