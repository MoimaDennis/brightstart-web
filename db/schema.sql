-- =====================================================================
-- BrightStart Education Support - database schema + sample data
-- Safe to re-run: it drops and re-creates every table.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS brightstart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brightstart;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tblusersettings, tblperformance, tblattendance, tblenrolment,
                     tblsession, tbldonation, tblvolunteer, tblprogram, tblstudent, tbladministrator;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- TABLES
-- ---------------------------------------------------------------------
CREATE TABLE tbladministrator (
    AdminID       INT          NOT NULL AUTO_INCREMENT,
    FullName      VARCHAR(100) NOT NULL,
    Email         VARCHAR(100) NOT NULL,
    Username      VARCHAR(50)  NOT NULL,
    PasswordHash  VARCHAR(255) NOT NULL,
    Role          ENUM('Admin','Coordinator','Volunteer','Management') NOT NULL DEFAULT 'Volunteer',
    Verified      TINYINT(1)   NOT NULL DEFAULT 0,
    AuthProvider  ENUM('local','google') NOT NULL DEFAULT 'local',
    CreatedAt     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (AdminID),
    UNIQUE KEY uq_admin_email (Email),
    UNIQUE KEY uq_admin_username (Username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblusersettings (
    SettingID           INT        NOT NULL AUTO_INCREMENT,
    AdminID             INT        NOT NULL,
    Theme               ENUM('dark','light')     NOT NULL DEFAULT 'dark',
    TextSize            ENUM('normal','large')   NOT NULL DEFAULT 'normal',
    ShowDashboardSlider TINYINT(1) NOT NULL DEFAULT 1,
    UpdatedAt           TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (SettingID),
    UNIQUE KEY uq_settings_admin (AdminID),
    CONSTRAINT fk_settings_admin FOREIGN KEY (AdminID)
        REFERENCES tbladministrator (AdminID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblstudent (
    StudentID      INT          NOT NULL AUTO_INCREMENT,
    FirstName      VARCHAR(50)  NOT NULL,
    LastName       VARCHAR(50)  NOT NULL,
    DateOfBirth    DATE         NOT NULL,
    Gender         ENUM('Male','Female','Other') NOT NULL,
    CommunityArea  VARCHAR(100) NOT NULL,
    ContactNumber  VARCHAR(20)  NOT NULL,
    EnrolmentDate  DATE         NOT NULL,
    PRIMARY KEY (StudentID),
    KEY idx_student_lastname (LastName),
    KEY idx_student_area (CommunityArea)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblprogram (
    ProgramID    INT          NOT NULL AUTO_INCREMENT,
    ProgramName  VARCHAR(100) NOT NULL,
    Description  TEXT         NULL,
    StartDate    DATE         NOT NULL,
    EndDate      DATE         NULL,
    Status       ENUM('Active','Pending','Closed') NOT NULL DEFAULT 'Active',
    PRIMARY KEY (ProgramID),
    UNIQUE KEY uq_program_name (ProgramName),
    CONSTRAINT chk_program_dates CHECK (EndDate IS NULL OR EndDate >= StartDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblvolunteer (
    VolunteerID        INT          NOT NULL AUTO_INCREMENT,
    FullName           VARCHAR(100) NOT NULL,
    ContactEmail       VARCHAR(100) NOT NULL,
    ContactNumber      VARCHAR(20)  NOT NULL,
    SubjectSpeciality  VARCHAR(100) NOT NULL,
    JoinDate           DATE         NOT NULL,
    Status             ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    PRIMARY KEY (VolunteerID),
    UNIQUE KEY uq_volunteer_email (ContactEmail)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblsession (
    SessionID    INT          NOT NULL AUTO_INCREMENT,
    ProgramID    INT          NOT NULL,
    VolunteerID  INT          NOT NULL,
    SessionDate  DATE         NOT NULL,
    StartTime    TIME         NOT NULL,
    EndTime      TIME         NOT NULL,
    Venue        VARCHAR(150) NOT NULL,
    PRIMARY KEY (SessionID),
    KEY idx_session_date (SessionDate),
    CONSTRAINT fk_session_program   FOREIGN KEY (ProgramID)
        REFERENCES tblprogram (ProgramID)     ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_session_volunteer FOREIGN KEY (VolunteerID)
        REFERENCES tblvolunteer (VolunteerID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_session_times CHECK (EndTime > StartTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblenrolment (
    EnrolmentID    INT  NOT NULL AUTO_INCREMENT,
    StudentID      INT  NOT NULL,
    ProgramID      INT  NOT NULL,
    EnrolmentDate  DATE NOT NULL,
    Status         ENUM('Active','Completed','Withdrawn') NOT NULL DEFAULT 'Active',
    PRIMARY KEY (EnrolmentID),
    UNIQUE KEY uq_enrolment_student_program (StudentID, ProgramID),
    CONSTRAINT fk_enrolment_student FOREIGN KEY (StudentID)
        REFERENCES tblstudent (StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_enrolment_program FOREIGN KEY (ProgramID)
        REFERENCES tblprogram (ProgramID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblattendance (
    AttendanceID      INT          NOT NULL AUTO_INCREMENT,
    SessionID         INT          NOT NULL,
    StudentID         INT          NOT NULL,
    AttendanceStatus  ENUM('Present','Absent','Excused') NOT NULL,
    Notes             VARCHAR(255) NULL,
    PRIMARY KEY (AttendanceID),
    UNIQUE KEY uq_attendance_session_student (SessionID, StudentID),
    CONSTRAINT fk_attendance_session FOREIGN KEY (SessionID)
        REFERENCES tblsession (SessionID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attendance_student FOREIGN KEY (StudentID)
        REFERENCES tblstudent (StudentID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tblperformance (
    PerformanceID   INT           NOT NULL AUTO_INCREMENT,
    StudentID       INT           NOT NULL,
    ProgramID       INT           NOT NULL,
    AssessmentDate  DATE          NOT NULL,
    Score           DECIMAL(5,2)  NOT NULL,
    Grade           CHAR(1)       NOT NULL,
    Comments        VARCHAR(255)  NULL,
    PRIMARY KEY (PerformanceID),
    CONSTRAINT fk_performance_student FOREIGN KEY (StudentID)
        REFERENCES tblstudent (StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_performance_program FOREIGN KEY (ProgramID)
        REFERENCES tblprogram (ProgramID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_performance_score CHECK (Score BETWEEN 0 AND 100),
    CONSTRAINT chk_performance_grade CHECK (Grade IN ('A','B','C','D','F'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tbldonation (
    DonationID      INT           NOT NULL AUTO_INCREMENT,
    DonorName       VARCHAR(100)  NOT NULL,
    DonorContact    VARCHAR(100)  NOT NULL,
    DonationAmount  DECIMAL(10,2) NOT NULL,
    DonationDate    DATE          NOT NULL,
    DonationType    ENUM('Cash','Bank Transfer','In-Kind') NOT NULL DEFAULT 'Cash',
    Notes           VARCHAR(255)  NULL,
    PRIMARY KEY (DonationID),
    KEY idx_donation_date (DonationDate),
    CONSTRAINT chk_donation_amount CHECK (DonationAmount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- SAMPLE DATA  (passwords: admin@brightstart.org = Admin@123, everyone else = Pass@1234)
-- ---------------------------------------------------------------------
INSERT INTO tbladministrator (FullName, Email, Username, PasswordHash, Role, Verified) VALUES
('Thandi Mokoena',  'admin@brightstart.org',          'admin',         '$2y$10$bveaTkN3mkITmvNM9VdrZO7jVY9AbJKWZKvhW7wHF95XmRBYt./OO', 'Admin',       1),
('Sipho Dlamini',   'coordinator@brightstart.org',    'sipho.dlamini', '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Coordinator', 1),
('Lerato Khumalo',  'lerato.khumalo@brightstart.org', 'lerato.k',      '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Volunteer',   1),
('Neo Mahlangu',    'neo.mahlangu@brightstart.org',   'neo.mahlangu',  '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Volunteer',   1),
('Palesa Ndlovu',   'palesa.ndlovu@brightstart.org',  'palesa.n',      '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Management',  1),
('Johan Botha',     'johan.botha@brightstart.org',    'johan.botha',   '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Coordinator', 1),
('Zanele Nkosi',    'zanele.nkosi@brightstart.org',   'zanele.n',      '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Volunteer',   1),
('Thabo Molefe',    'thabo.molefe@brightstart.org',   'thabo.m',       '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Volunteer',   1),
('Ayesha Patel',    'ayesha.patel@brightstart.org',   'ayesha.p',      '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Volunteer',   0),
('Bongani Zulu',    'bongani.zulu@brightstart.org',   'bongani.z',     '$2y$10$Vn05l1NgjMSxaKLXclQRI.hOBzI8yThyi4czLHfsJXyFdFUcPAEKG', 'Management',  0);

INSERT INTO tblusersettings (AdminID, Theme, TextSize, ShowDashboardSlider) VALUES
(1,'dark','normal',1),(2,'dark','normal',1),(3,'light','normal',1),(4,'dark','large',1),(5,'dark','normal',0),
(6,'light','normal',1),(7,'dark','normal',1),(8,'dark','large',0),(9,'dark','normal',1),(10,'light','large',1);

INSERT INTO tblstudent (FirstName, LastName, DateOfBirth, Gender, CommunityArea, ContactNumber, EnrolmentDate) VALUES
('Lindiwe',   'Mthembu',   '2009-03-14', 'Female', 'Soweto',      '0711234501', '2026-02-02'),
('Sibusiso',  'Ngcobo',    '2008-07-22', 'Male',   'Alexandra',   '0711234502', '2026-02-02'),
('Naledi',    'Sithole',   '2010-01-05', 'Female', 'Mamelodi',    '0711234503', '2026-02-03'),
('Tshepo',    'Maseko',    '2009-11-30', 'Male',   'Soshanguve',  '0711234504', '2026-02-03'),
('Ayanda',    'Zungu',     '2008-05-18', 'Female', 'Khayelitsha', '0711234505', '2026-02-09'),
('Karabo',    'Radebe',    '2010-09-09', 'Male',   'Soweto',      '0711234506', '2026-02-09'),
('Nomvula',   'Dube',      '2009-12-25', 'Female', 'Tembisa',     '0711234507', '2026-02-10'),
('Mpho',      'Chauke',    '2008-02-11', 'Male',   'Alexandra',   '0711234508', '2026-02-10'),
('Refilwe',   'Mokwena',   '2010-06-16', 'Female', 'Mamelodi',    '0711234509', '2026-02-16'),
('Bheki',     'Shabalala', '2009-08-03', 'Male',   'Soshanguve',  '0711234510', '2026-02-16'),
('Zinhle',    'Cele',      '2008-10-27', 'Female', 'Tembisa',     '0711234511', '2026-02-17'),
('Thabiso',   'Nkuna',     '2010-04-12', 'Other',  'Khayelitsha', '0711234512', '2026-02-17');

INSERT INTO tblprogram (ProgramName, Description, StartDate, EndDate, Status) VALUES
('Maths Mentorship',        'Weekly one-on-one maths tutoring for Grade 10 to 12 learners.',          '2026-02-01', '2026-11-30', 'Active'),
('Science Lab Club',        'Hands-on experiments and science fair preparation.',                     '2026-02-01', '2026-11-30', 'Active'),
('English Literacy Boost',  'Reading, writing and comprehension support.',                            '2026-02-15', '2026-10-31', 'Active'),
('Coding Club',             'Introduction to programming and logical thinking.',                      '2026-03-01', '2026-12-15', 'Active'),
('Matric Revision Camp',    'Intensive exam revision with past papers.',                              '2026-09-01', '2026-11-15', 'Active'),
('Career Guidance',         'Career exploration, bursary applications and CV workshops.',             '2026-03-15', '2026-11-30', 'Active'),
('Study Skills Workshop',   'Time management, note taking and exam technique.',                       '2026-04-01', '2026-09-30', 'Active'),
('Financial Literacy',      'Budgeting, saving and entrepreneurship basics.',                         '2027-01-15', '2027-06-30', 'Pending'),
('Creative Arts Studio',    'Drawing, music and drama sessions after school.',                        '2025-02-01', '2025-11-30', 'Closed'),
('Life Skills and Wellbeing','Confidence, communication and mental wellbeing sessions.',              '2025-03-01', '2025-10-31', 'Closed');

INSERT INTO tblvolunteer (FullName, ContactEmail, ContactNumber, SubjectSpeciality, JoinDate, Status) VALUES
('Lerato Khumalo',  'lerato.khumalo@volunteer.org', '0821110001', 'Mathematics',        '2025-06-01', 'Active'),
('Neo Mahlangu',    'neo.mahlangu@volunteer.org',   '0821110002', 'Physical Sciences',  '2025-06-10', 'Active'),
('Zanele Nkosi',    'zanele.nkosi@volunteer.org',   '0821110003', 'English',            '2025-07-05', 'Active'),
('Thabo Molefe',    'thabo.molefe@volunteer.org',   '0821110004', 'Computer Science',   '2025-07-20', 'Active'),
('Pieter van Wyk',  'pieter.vanwyk@volunteer.org',  '0821110005', 'Accounting',         '2025-08-15', 'Active'),
('Nandi Zulu',      'nandi.zulu@volunteer.org',     '0821110006', 'Life Orientation',   '2025-09-01', 'Active'),
('Sarah Naidoo',    'sarah.naidoo@volunteer.org',   '0821110007', 'Career Counselling', '2025-09-12', 'Active'),
('Musa Ndaba',      'musa.ndaba@volunteer.org',     '0821110008', 'Mathematics',        '2025-10-02', 'Active'),
('Fatima Cassim',   'fatima.cassim@volunteer.org',  '0821110009', 'Arts and Drama',     '2024-05-01', 'Inactive'),
('Daniel Smit',     'daniel.smit@volunteer.org',    '0821110010', 'Life Sciences',      '2024-06-01', 'Inactive');

INSERT INTO tblsession (ProgramID, VolunteerID, SessionDate, StartTime, EndTime, Venue) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 21 DAY), '15:00:00', '16:30:00', 'Soweto Community Hall'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 18 DAY), '14:00:00', '16:00:00', 'Alexandra Library'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 14 DAY), '15:30:00', '17:00:00', 'Mamelodi Youth Centre'),
(4, 4, DATE_SUB(CURDATE(), INTERVAL 10 DAY), '13:00:00', '15:00:00', 'Soshanguve Computer Lab'),
(1, 8, DATE_SUB(CURDATE(), INTERVAL 7 DAY),  '15:00:00', '16:30:00', 'Soweto Community Hall'),
(6, 7, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  '10:00:00', '12:00:00', 'Tembisa Community Centre'),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  '15:00:00', '16:30:00', 'Soweto Community Hall'),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 4 DAY),  '14:00:00', '16:00:00', 'Alexandra Library'),
(5, 5, DATE_ADD(CURDATE(), INTERVAL 6 DAY),  '09:00:00', '12:00:00', 'Khayelitsha Skills Centre'),
(4, 4, DATE_ADD(CURDATE(), INTERVAL 9 DAY),  '13:00:00', '15:00:00', 'Soshanguve Computer Lab'),
(7, 6, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '14:00:00', '15:30:00', 'Mamelodi Youth Centre'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '15:30:00', '17:00:00', 'Mamelodi Youth Centre');

INSERT INTO tblenrolment (StudentID, ProgramID, EnrolmentDate, Status) VALUES
(1, 1, '2026-02-03', 'Active'),    (2, 1, '2026-02-03', 'Active'),    (3, 3, '2026-02-04', 'Active'),
(4, 4, '2026-02-04', 'Active'),    (5, 2, '2026-02-10', 'Active'),    (6, 1, '2026-02-10', 'Active'),
(7, 6, '2026-02-11', 'Active'),    (8, 2, '2026-02-11', 'Active'),    (9, 3, '2026-02-17', 'Active'),
(10, 4, '2026-02-17', 'Completed'),(11, 6, '2026-02-18', 'Active'),   (12, 5, '2026-02-18', 'Withdrawn'),
(1, 2, '2026-03-02', 'Active'),    (3, 7, '2026-03-02', 'Active');

INSERT INTO tblattendance (SessionID, StudentID, AttendanceStatus, Notes) VALUES
(1, 1, 'Present', 'Good participation'),      (1, 2, 'Present', NULL),
(1, 6, 'Absent',  'No reason given'),         (2, 5, 'Present', 'Completed lab report'),
(2, 8, 'Excused', 'Family event'),            (2, 1, 'Present', NULL),
(3, 3, 'Present', 'Read aloud confidently'),  (3, 9, 'Present', NULL),
(4, 4, 'Present', 'Built first web page'),    (4, 10, 'Absent', NULL),
(5, 1, 'Present', NULL),                      (5, 2, 'Absent',  'Sick'),
(6, 7, 'Present', 'Asked good questions'),    (6, 11, 'Present', NULL);

INSERT INTO tblperformance (StudentID, ProgramID, AssessmentDate, Score, Grade, Comments) VALUES
(1, 1, '2026-03-20', 82.50, 'A', 'Excellent progress in algebra'),
(2, 1, '2026-03-20', 68.00, 'C', 'Needs more practice with geometry'),
(3, 3, '2026-03-25', 74.00, 'B', 'Improving comprehension'),
(4, 4, '2026-04-02', 91.00, 'A', 'Strong logical thinking'),
(5, 2, '2026-04-08', 77.50, 'B', 'Good lab work'),
(6, 1, '2026-04-08', 55.00, 'D', 'Attendance affecting results'),
(7, 6, '2026-04-15', 63.00, 'C', 'Clear career interests'),
(8, 2, '2026-04-15', 48.00, 'F', 'Requires extra support'),
(9, 3, '2026-04-22', 85.00, 'A', 'Great essay writing'),
(10, 4, '2026-04-22', 79.00, 'B', 'Completed the coding course'),
(11, 6, '2026-05-06', 71.00, 'B', 'Good CV draft'),
(1, 2, '2026-05-06', 66.00, 'C', 'Steady improvement');

INSERT INTO tbldonation (DonorName, DonorContact, DonationAmount, DonationDate, DonationType, Notes) VALUES
('Nkosana Trading',        'accounts@nkosana.co.za',  5000.00, '2026-02-12', 'Bank Transfer', 'Annual sponsorship'),
('Mary-Ann Fourie',        'maryann.fourie@mail.com',  250.00, '2026-02-20', 'Cash',          NULL),
('Pretoria Rotary Club',   'info@pretoriarotary.org', 3000.00, '2026-03-05', 'Bank Transfer', 'Stationery fund'),
('Ahmed Hassan',           'ahmed.hassan@mail.com',    500.00, '2026-03-18', 'Cash',          NULL),
('Bright Futures Trust',   'grants@brightfutures.org',12000.00,'2026-04-02', 'Bank Transfer', 'Programme grant'),
('Lindiwe Zondi',          'lindiwe.zondi@mail.com',   150.00, '2026-04-14', 'Cash',          'Monthly donor'),
('TechSA Pty Ltd',         'csi@techsa.co.za',        2500.00, '2026-05-06', 'In-Kind',       '5 refurbished laptops'),
('Jacques Pretorius',      'jacques.p@mail.com',       750.00, '2026-05-21', 'Bank Transfer', NULL),
('Community Church Soweto','office@ccsoweto.org',     1800.00, '2026-06-10', 'Cash',          'Collection drive'),
('Anonymous',              'anonymous@brightstart.org',300.00, '2026-07-02', 'Cash',          NULL),
('Green Valley Bakery',    'hello@greenvalley.co.za',  600.00, '2026-08-15', 'In-Kind',       'Snacks for sessions'),
('Priya Naicker',          'priya.naicker@mail.com',   400.00, '2026-09-09', 'Bank Transfer', NULL);