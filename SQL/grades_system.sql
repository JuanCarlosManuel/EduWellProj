-- EduWell Grade Tracking System Database Schema
-- Run this SQL in your phpMyAdmin or MySQL console

-- Step 1: Add user role column to users table
ALTER TABLE `users` ADD COLUMN `role` ENUM('student', 'teacher', 'admin') DEFAULT 'student' AFTER `email`;

-- Step 2: Create courses table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 3: Create enrollments table (students enrolled in courses)
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_course` (`student_id`, `course_id`),
  KEY `course_id` (`course_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 4: Create assignments table
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `max_score` decimal(5,2) NOT NULL DEFAULT 100.00,
  `due_date` date DEFAULT NULL,
  `assignment_type` varchar(50) DEFAULT 'Assignment',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 5: Create grades table
CREATE TABLE IF NOT EXISTS `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `letter_grade` varchar(2) DEFAULT NULL,
  `feedback` text,
  `graded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_assignment` (`student_id`, `assignment_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 6: Create performance_reports table (for analytics)
CREATE TABLE IF NOT EXISTS `performance_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `overall_average` decimal(5,2) NOT NULL,
  `total_assignments` int(11) DEFAULT 0,
  `completed_assignments` int(11) DEFAULT 0,
  `improvement_trend` varchar(20) DEFAULT NULL,
  `weak_areas` text,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `course_id` (`course_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 7: Create trigger to calculate percentage and letter grade automatically
DELIMITER //
CREATE TRIGGER calculate_grade AFTER INSERT ON grades
FOR EACH ROW
BEGIN
    UPDATE grades 
    SET percentage = (NEW.score / NEW.max_score) * 100,
        letter_grade = CASE 
            WHEN (NEW.score / NEW.max_score) * 100 >= 90 THEN 'A'
            WHEN (NEW.score / NEW.max_score) * 100 >= 80 THEN 'B'
            WHEN (NEW.score / NEW.max_score) * 100 >= 70 THEN 'C'
            WHEN (NEW.score / NEW.max_score) * 100 >= 60 THEN 'D'
            ELSE 'F'
        END
    WHERE id = NEW.id;
END//
DELIMITER ;

-- Step 8: Create trigger for updates
DELIMITER //
CREATE TRIGGER calculate_grade_update AFTER UPDATE ON grades
FOR EACH ROW
BEGIN
    UPDATE grades 
    SET percentage = (NEW.score / NEW.max_score) * 100,
        letter_grade = CASE 
            WHEN (NEW.score / NEW.max_score) * 100 >= 90 THEN 'A'
            WHEN (NEW.score / NEW.max_score) * 100 >= 80 THEN 'B'
            WHEN (NEW.score / NEW.max_score) * 100 >= 70 THEN 'C'
            WHEN (NEW.score / NEW.max_score) * 100 >= 60 THEN 'D'
            ELSE 'F'
        END
    WHERE id = NEW.id;
END//
DELIMITER ;

