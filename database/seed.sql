USE academic_events;

-- Default manager account
-- Password: Admin1234
INSERT INTO users (full_name, email, password, role) VALUES (
    'Admin Manager',
    'admin@college.edu',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'manager'
);

-- Default student account
-- Password: Student1234
INSERT INTO users (full_name, email, password, role) VALUES (
    'Test Student',
    'student@college.edu',
    '$2y$10$TKh8H1.PfbuNf0/9J.nVxuVAYzKFf6sH6gMnJ0OJ3h6eFiP6j4j46',
    'student'
);

-- Sample events
INSERT INTO events (title, description, event_date, location, created_by) VALUES
    ('Orientation Day',       'Welcome session for all new students.',          '2026-06-01', 'Main Hall',            1),
    ('AI & Machine Learning', 'Workshop covering ML fundamentals.',             '2026-06-15', 'CS Building, Room 3',  1),
    ('Annual Science Fair',   'Student project exhibitions and presentations.', '2026-07-10', 'Sports Complex',       1);
