# EduWell Grade Tracking System - Setup Guide

## Overview

The EduWell system now includes comprehensive grade tracking features that align with your objectives. This guide will help you set up and use the new features.

## Database Setup

### Step 1: Run the SQL Schema

1. Open phpMyAdmin or your MySQL client
2. Select the `eduwell` database
3. Open the SQL tab
4. Copy and paste the contents of `SQL/grades_system.sql`
5. Execute the SQL script

This will create:
- `courses` table - Stores course information
- `enrollments` table - Links students to courses
- `assignments` table - Stores assignment details
- `grades` table - Stores student grades
- `performance_reports` table - Stores analytics data
- Updates `users` table with a `role` column

### Step 2: Set Up User Roles

After running the SQL, manually set user roles in the database:

```sql
-- Set a user as teacher
UPDATE users SET role = 'teacher' WHERE email = 'teacher@example.com';

-- Set a user as admin
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';

-- Students default to 'student' role
```

## File Structure

### New Files Created:

**Student Pages:**
- `grades.php` - View all grades by course
- `reports.php` - Performance reports with trends
- `analytics.php` - Data-driven analytics and recommendations

**Teacher Pages:**
- `teacher_dashboard.php` - Main teacher interface
- `create_course.php` - Create new courses
- `manage_course.php` - Manage course assignments and students
- `add_assignment.php` - Create assignments
- `add_grade.php` - Enter grades for students
- `course_students.php` - Manage student enrollments

**Components:**
- `navbar.php` - Reusable navigation component
- `footer.php` - Reusable footer component

**API Endpoints:**
- `api/get_grades.php` - Get grades JSON API
- `api/check_updates.php` - Check for grade updates

**Database:**
- `SQL/grades_system.sql` - Database schema

## Usage Guide

### For Teachers:

1. **Create a Course:**
   - Go to Teacher Dashboard
   - Click "Create New Course"
   - Enter course code, name, semester, and year

2. **Add Students:**
   - Open the course management page
   - Click "Manage Students"
   - Enter student email addresses to enroll them

3. **Create Assignments:**
   - Go to "Add Assignment"
   - Select course, enter title, type, max score, and due date

4. **Enter Grades:**
   - Go to course management
   - Click "Enter Grades" on an assignment
   - Enter scores and optional feedback for each student
   - Grades are automatically calculated (percentage and letter grade)

### For Students:

1. **View Grades:**
   - Navigate to "Grades" from the main menu
   - See all courses with average scores
   - View individual assignment grades

2. **Performance Reports:**
   - Go to "Reports" page
   - View course-by-course performance
   - See trends and improvement analysis

3. **Analytics:**
   - Visit "Analytics" page
   - Get personalized recommendations
   - See grade distribution
   - Identify areas needing attention

## Features Implemented

✅ **Grade Tracking** - Students can view all their grades organized by course
✅ **Real-time Updates** - API endpoints for checking grade updates
✅ **Teacher Dashboard** - Complete interface for teachers to manage courses
✅ **Assignment Management** - Create and manage assignments
✅ **Grade Entry** - Teachers can enter grades with automatic calculation
✅ **Performance Reports** - Detailed reports showing student growth
✅ **Analytics** - Data-driven analysis and recommendations
✅ **Trend Analysis** - Identify improving/declining performance
✅ **Weak Areas Detection** - Automatically identifies courses needing attention
✅ **Course Management** - Full CRUD operations for courses
✅ **Student Enrollment** - Manage student enrollments per course

## Grade Calculation

Grades are automatically calculated:
- **Percentage:** (score / max_score) × 100
- **Letter Grade:**
  - A: 90-100%
  - B: 80-89%
  - C: 70-79%
  - D: 60-69%
  - F: Below 60%

## API Endpoints

### Get Grades
```
GET api/get_grades.php?course_id=1
```
Returns JSON with all grades for the logged-in student.

### Check for Updates
```
GET api/check_updates.php?last_check=2025-01-01 12:00:00
```
Returns JSON with grades updated since the last check timestamp.

## Navigation

The navbar automatically shows:
- **Students:** Home, Profile, Contact, Grades, Reports, Analytics
- **Teachers:** Home, Profile, Contact, Teacher Dashboard
- **All Users:** Login/Signup or Logout

## Next Steps

1. Run the SQL schema file
2. Set at least one user as a teacher
3. Create a test course
4. Enroll test students
5. Create assignments
6. Enter grades
7. Test the student views

## Troubleshooting

**Issue:** Grades not showing
- Check that students are enrolled in courses
- Verify assignments exist for the course
- Ensure grades have been entered

**Issue:** Teacher dashboard not accessible
- Verify user role is set to 'teacher' in database
- Check session is active

**Issue:** SQL errors
- Ensure MySQL version supports the syntax
- Check that foreign key constraints are enabled
- Verify `users` table exists before running schema

## Notes

- All grades are stored in the database (not localStorage)
- Grade calculations happen automatically via triggers
- Performance reports are generated on-demand
- Analytics are calculated in real-time from grade data

