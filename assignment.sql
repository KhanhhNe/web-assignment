-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 19, 2024 at 04:19 PM
-- Server version: 5.7.39
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `assignment`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
    `id` int(11) NOT NULL, `creator_teacher_id` int(11) NOT NULL, `course_name` varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
    `id` int(11) NOT NULL, `quiz_id` int(11) NOT NULL, `difficulty` varchar(255) NOT NULL, `question` text NOT NULL, `image_url` varchar(255) DEFAULT NULL, `answers` json NOT NULL, `correct_answer` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `quizes`
--

CREATE TABLE `quizes` (
    `id` int(11) NOT NULL, `teacher_id` int(11) NOT NULL, `course_id` int(11) NOT NULL, `name` varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
    `id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `email` varchar(255) NOT NULL, `password` varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
ADD PRIMARY KEY (`id`),
ADD KEY `creator_teacher_id` (`creator_teacher_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quizes`
--
ALTER TABLE `quizes`
ADD PRIMARY KEY (`id`),
ADD KEY `teacher_id` (`teacher_id`),
ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizes`
--
ALTER TABLE `quizes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`creator_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizes`
--
ALTER TABLE `quizes`
ADD CONSTRAINT `quizes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `quizes_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;

-- all seed data need realistic data
-- seed data for teachers
INSERT INTO
    `teachers` (
        `id`, `name`, `email`, `password`
    )
VALUES (
        1, 'John', 'john@example.com', 'password'
    ),
    (
        2, 'Jane', 'jane@example.com', 'password'
    ),
    (
        3, 'Bob', 'bob@example.com', 'password'
    ),
    (
        4, 'Alice', 'alice@example.com', 'password'
    );

-- seed data for courses
INSERT INTO
    `courses` (
        `id`, `creator_teacher_id`, `course_name`
    )
VALUES (1, 1, 'Math'),
    (2, 2, 'English'),
    (3, 3, 'Science'),
    (4, 4, 'History');

-- seed data for quizes
INSERT INTO
    `quizes` (
        `id`, `teacher_id`, `course_id`, `name`
    )
VALUES (1, 1, 1, 'Math Quiz'),
    (2, 2, 2, 'English Quiz'),
    (3, 3, 3, 'Science Quiz'),
    (4, 4, 4, 'History Quiz');

-- seed data for questions
INSERT INTO
    `questions` (
        `id`, `quiz_id`, `difficulty`, `question`, `image_url`, `answers`, `correct_answer`
    )
VALUES (
        1, 1, 'easy', 'What is the capital of France?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        2, 1, 'medium', 'What is the largest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        3, 1, 'hard', 'What is the smallest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        4, 1, 'easy', 'What is the capital of France?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        5, 1, 'medium', 'What is the largest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        6, 1, 'hard', 'What is the smallest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        7, 2, 'easy', 'What is the capital of France?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        8, 2, 'medium', 'What is the largest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        9, 2, 'hard', 'What is the smallest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        10, 3, 'easy', 'What is the capital of France?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        11, 3, 'medium', 'What is the largest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        12, 3, 'hard', 'What is the smallest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        13, 4, 'easy', 'What is the capital of France?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        14, 4, 'medium', 'What is the largest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    ),
    (
        15, 4, 'hard', 'What is the smallest country in the world?', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_France.svg/200px-Flag_of_France.svg.png', '["Paris", "London", "Berlin", "Rome"]', 1
    );