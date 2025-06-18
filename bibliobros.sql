CREATE DATABASE bibliobros;
USE bibliobros;

-- 3) Universities table
CREATE TABLE universities (
  id   SERIAL   PRIMARY KEY,
  name TEXT     UNIQUE NOT NULL
);

-- 4) Faculties table (belongs to a university) with description
CREATE TABLE faculties (
  id             SERIAL   PRIMARY KEY,
  university_id  INT      NOT NULL
    REFERENCES universities(id) ON DELETE CASCADE,
  name           TEXT     NOT NULL,
  description    TEXT,
  UNIQUE(university_id, name)
);

-- 5) Subjects table (will belong to a faculty)
CREATE TABLE subjects (
  id          SERIAL   PRIMARY KEY,
  faculty_id  INT      NOT NULL
    REFERENCES faculties(id) ON DELETE CASCADE,
  name        TEXT     NOT NULL,
  UNIQUE(faculty_id, name)
);

-- 6) Users table
CREATE TABLE users (
  id                 SERIAL PRIMARY KEY,
  fullname           TEXT   NOT NULL,
  email              TEXT   UNIQUE NOT NULL,
  password_hash      TEXT   NOT NULL,
  university_id      INT    NOT NULL
    REFERENCES universities(id) ON DELETE RESTRICT,
  public_description TEXT
);



-- 7) Mentor-subject link table
CREATE TABLE mentor_subject (
  user_id    INT NOT NULL
    REFERENCES users(id) ON DELETE CASCADE,
  subject_id INT NOT NULL
    REFERENCES subjects(id) ON DELETE CASCADE,
  intro      TEXT,
  PRIMARY KEY(user_id, subject_id)
);

-- 8) Help requests
CREATE TABLE requests (
  id          SERIAL PRIMARY KEY,
  subject_id  INT    NOT NULL
    REFERENCES subjects(id) ON DELETE CASCADE,
  mentee_id   INT    NOT NULL
    REFERENCES users(id) ON DELETE CASCADE,
  mentor_id   INT    REFERENCES users(id),
  message     TEXT   NOT NULL,
  status      TEXT   NOT NULL DEFAULT 'pending'
    CHECK(status IN ('pending','accepted','rejected','closed')),
  created_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 9) Chats and messages
CREATE TABLE chats (
  id          SERIAL PRIMARY KEY,
  request_id  INT NOT NULL
    REFERENCES requests(id) ON DELETE CASCADE,
  created_at  TIMESTAMP NOT NULL DEFAULT NOW(),
  active      BOOLEAN NOT NULL DEFAULT TRUE  -- ← new flag
);


CREATE TABLE messages (
  id         SERIAL PRIMARY KEY,
  chat_id    INT    NOT NULL
    REFERENCES chats(id) ON DELETE CASCADE,
  sender_id  INT    NOT NULL
    REFERENCES users(id) ON DELETE CASCADE,
  content    TEXT   NOT NULL,
  timestamp  TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 10) Ratings
CREATE TABLE ratings (
  id         SERIAL PRIMARY KEY,
  chat_id    INT NOT NULL
    REFERENCES chats(id) ON DELETE CASCADE,
  score      INT NOT NULL CHECK(score BETWEEN 1 AND 5),
  comment    TEXT
);

-- 11) Map each user’s chosen role for a subject
CREATE TABLE user_subject_role (
  user_id    INT    NOT NULL
    REFERENCES users(id) ON DELETE CASCADE,
  subject_id INT    NOT NULL
    REFERENCES subjects(id) ON DELETE CASCADE,
  role       TEXT   NOT NULL
    CHECK (role IN ('mentor','mentee')),
  PRIMARY KEY (user_id, subject_id)
);


-- 12) Sample data: insert universities
INSERT INTO universities (name) VALUES
  ('Sapienza University of Rome'),
  ('University of Rome Tor Vergata'),
  ('Roma Tre University'),
  ('Foro Italico University');

-- Insert faculties with detailed English descriptions
INSERT INTO faculties (university_id, name, description) VALUES
  -- Sapienza University of Rome (id = 1)
  (1, 'Architecture', 
     'School of Architecture and Urban Planning, focusing on architectural design, sustainable urbanism, and heritage conservation.'),
  (1, 'Economics', 
     'School of Economics and Business, offering studies in macroeconomics, finance, and international trade.'),
  (1, 'Pharmacy and Medicine', 
     'Faculty of Pharmacy and Medicine, dedicated to biomedical research, clinical pharmacology, and public health.'),
  (1, 'Law', 
     'Faculty of Law, covering civil, criminal, international law, and human rights advocacy.'),
  (1, 'Civil and Industrial Engineering', 
     'Engineering faculty specializing in structural engineering, construction management, and industrial systems.'),
  (1, 'Information Engineering, Computer Science and Statistics', 
     'Faculty covering data science, artificial intelligence, network engineering, and applied statistics.'),
  (1, 'Humanities and Philosophy', 
     'Faculty of Humanities, including literature studies, philosophy, linguistics, and cultural analysis.'),
  (1, 'Medicine and Dentistry', 
     'Faculty of Medicine and Dentistry, with programs in clinical medicine, dental surgery, and oral health.'),
  (1, 'Medicine and Psychology', 
     'Interdisciplinary faculty combining medical sciences and clinical psychology for holistic care.'),
  (1, 'Mathematical, Physical and Natural Sciences', 
     'Faculty dedicated to pure mathematics, theoretical physics, chemistry, and environmental biology.'),
  (1, 'Political Science, Sociology and Communication', 
     'Faculty exploring public policy, social theory, and media studies.');
-- 13) Sample subjects for testing the web functionality (with explicit IDs)
INSERT INTO subjects (id, faculty_id, name) VALUES
  -- Architecture (faculty_id = 1)
  ( 1, 1, 'History of Architecture I'),
  ( 2, 1, 'Architectural Design Studio I'),
  ( 3, 1, 'Building Materials and Construction Techniques'),
  ( 4, 1, 'Structural Mechanics for Architects'),
  -- Economics (faculty_id = 2)
  ( 5, 2, 'Principles of Microeconomics'),
  ( 6, 2, 'Principles of Macroeconomics'),
  ( 7, 2, 'Introduction to Econometrics'),
  -- Business Administration (faculty_id = 3)
  ( 8, 3, 'Financial Accounting I'),
  ( 9, 3, 'Principles of Management'),
  (10, 3, 'Marketing Fundamentals'),
  -- Sport and Exercise Sciences (faculty_id = 4)
  (11, 4, 'Human Anatomy and Physiology'),
  (12, 4, 'Kinesiology and Biomechanics'),
  (13, 4, 'Exercise Prescription and Programming');


-- 14) Sample users for demo
INSERT INTO users (id, fullname, email, password_hash, university_id, public_description) VALUES
  (1, 'Alice Rossi',  'alice@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Economics student at Sapienza University'),
  (2, 'Marco Bianchi','marco@example.com','$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Teaching Assistant in Microeconomics'),
  (3, 'Giulia Verdi', 'giulia@example.com','$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Business and marketing expert'),
  (4, 'Luca Neri',   'luca@example.com',  '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Business Administration undergraduate');

-- 15) Assign each user’s role for specific subjects
INSERT INTO user_subject_role (user_id, subject_id, role) VALUES
  -- Alice as mentee in Economics subjects
  (1, 5, 'mentee'),   -- Principles of Microeconomics
  (1, 9, 'mentee'),   -- Principles of Management
  -- Luca as mentee in Management and Marketing
  (4, 9, 'mentee'),   -- Principles of Management
  (4,10, 'mentee'),   -- Marketing Fundamentals
  -- Marco as mentor in Microeconomics and Econometrics
  (2, 5, 'mentor'),   -- Principles of Microeconomics
  (2, 7, 'mentor'),   -- Introduction to Econometrics
  -- Giulia as mentor in Management and Marketing
  (3, 9, 'mentor'),   -- Principles of Management
  (3,10, 'mentor');   -- Marketing Fundamentals

-- 16) Mentor introductions for their subjects
INSERT INTO mentor_subject (user_id, subject_id, intro) VALUES
  (2,  5, 'I teach Microeconomics at Sapienza University'),
  (2,  7, 'Passionate about data analysis and econometrics'),
  (3,  9, 'I love helping students understand business concepts'),
  (3, 10, 'Marketing made simple with real-world examples');
