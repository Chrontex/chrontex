-- Sample/mock data for local testing.
-- Run AFTER schema.sql. Safe to re-run: it clears old sample rows first.
--
-- Creates one seed admin account so events have a valid created_by,
-- plus 10 upcoming events (2 per category) and 2 announcements.
-- Dates are relative to CURDATE(), so this stays useful no matter when you run it.
--
-- Seed admin login: admin@nsbm.ac.lk / Admin@123
-- Register your own student account through student/register.php with your
-- @students.nsbm.ac.lk email to test the student side against this data.

USE chrontex;

DELETE FROM registrations;
DELETE FROM announcements;
DELETE FROM events;
DELETE FROM users WHERE email = 'admin@nsbm.ac.lk';

INSERT INTO users (full_name, email, password, role, status)
VALUES ('Seed Admin', 'admin@nsbm.ac.lk', '$2y$12$jSzItsyQKUAOhsyZWmfK4OiwTBjWKkNjn4ciOdoc2WawgKby49P3W', 'admin', 'active');

SET @admin_id = (SELECT user_id FROM users WHERE email = 'admin@nsbm.ac.lk');

INSERT INTO events (title, description, category_id, event_date, start_time, end_time, venue, capacity, created_by)
VALUES
    ('Inter-Faculty Football Tournament',
     'Faculties compete for the annual football trophy. Come cheer for your team!',
     (SELECT category_id FROM categories WHERE category_name = 'Sports'),
     DATE_ADD(CURDATE(), INTERVAL 5 DAY), '15:00:00', '18:00:00', 'Main Ground', 100, @admin_id),

    ('Basketball 3v3 Championship',
     'Fast-paced 3-on-3 knockout tournament open to all students.',
     (SELECT category_id FROM categories WHERE category_name = 'Sports'),
     DATE_ADD(CURDATE(), INTERVAL 20 DAY), '09:00:00', '13:00:00', 'Indoor Court', 32, @admin_id),

    ('AI & Machine Learning Hackathon',
     '24-hour hackathon building ML-powered projects. Teams of up to 4.',
     (SELECT category_id FROM categories WHERE category_name = 'Technology'),
     DATE_ADD(CURDATE(), INTERVAL 10 DAY), '08:00:00', NULL, 'Auditorium', 60, @admin_id),

    ('Web Development Bootcamp',
     'Hands-on session covering HTML, CSS, Bootstrap and PHP basics.',
     (SELECT category_id FROM categories WHERE category_name = 'Technology'),
     DATE_ADD(CURDATE(), INTERVAL 12 DAY), '10:00:00', '16:00:00', 'Computer Lab 1', 30, @admin_id),

    ('Annual Cultural Night',
     'An evening of music, dance and drama celebrating our diversity.',
     (SELECT category_id FROM categories WHERE category_name = 'Cultural'),
     DATE_ADD(CURDATE(), INTERVAL 15 DAY), '18:00:00', '21:00:00', 'Open Air Theatre', 300, @admin_id),

    ('Dance & Music Fusion Fest',
     'Student bands and dance troupes perform live.',
     (SELECT category_id FROM categories WHERE category_name = 'Cultural'),
     DATE_ADD(CURDATE(), INTERVAL 25 DAY), '17:00:00', '20:00:00', 'Main Auditorium', 150, @admin_id),

    ('Resume Writing Workshop',
     'Learn how to write a resume that gets shortlisted, with live reviews.',
     (SELECT category_id FROM categories WHERE category_name = 'Workshop'),
     DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '15:00:00', 'Seminar Hall B', 40, @admin_id),

    ('Public Speaking Workshop',
     'Small-group workshop on confident public speaking. Limited seats.',
     (SELECT category_id FROM categories WHERE category_name = 'Workshop'),
     DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '11:00:00', 'Seminar Hall A', 2, @admin_id),

    ('Photography Club Meetup',
     'Monthly meetup — bring your camera and share your latest shots.',
     (SELECT category_id FROM categories WHERE category_name = 'Club'),
     DATE_ADD(CURDATE(), INTERVAL 7 DAY), '16:00:00', '18:00:00', 'Room 204', 25, @admin_id),

    ('Chess Club Weekly Tournament',
     'Casual Swiss-format tournament, all skill levels welcome.',
     (SELECT category_id FROM categories WHERE category_name = 'Club'),
     DATE_ADD(CURDATE(), INTERVAL 4 DAY), '17:00:00', '19:00:00', 'Library Discussion Room', 16, @admin_id);

INSERT INTO announcements (title, message, event_id, posted_by)
VALUES
    ('Welcome to Chrontex',
     'The Fall semester event calendar is live — browse upcoming events and register from the Browse Events page.',
     NULL, @admin_id),

    ('Public Speaking Workshop — almost full',
     'Only 2 seats available for the Public Speaking Workshop. Register early to secure your spot!',
     (SELECT event_id FROM events WHERE title = 'Public Speaking Workshop'), @admin_id);
