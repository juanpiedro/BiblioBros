-- setup_bibliobros.sql

-- 1) Creamos la base de datos y nos conectamos
CREATE DATABASE bibliobros;
\c bibliobros

-- 2) Borramos tablas por si existían
DROP TABLE IF EXISTS ratings, messages, chats, requests, mentors, users CASCADE;

-- 3) USERS TABLE (sin columna role y sin coma extra)
CREATE TABLE users (
    id            SERIAL PRIMARY KEY,
    fullname      TEXT NOT NULL,
    email         TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    university    TEXT NOT NULL
);

-- 4) MENTORS TABLE
CREATE TABLE mentors (
    id       SERIAL PRIMARY KEY,
    user_id  INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    subject  TEXT NOT NULL,
    intro    TEXT
);

-- 5) REQUESTS TABLE
CREATE TABLE requests (
    id         SERIAL PRIMARY KEY,
    subject    TEXT NOT NULL,
    mentee_id  INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    mentor_id  INT REFERENCES users(id),
    message    TEXT NOT NULL,
    status     TEXT NOT NULL DEFAULT 'pending'
               CHECK (status IN ('pending', 'accepted', 'rejected'))
);

-- 6) CHATS TABLE
CREATE TABLE chats (
    id         SERIAL PRIMARY KEY,
    request_id INT NOT NULL REFERENCES requests(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 7) MESSAGES TABLE
CREATE TABLE messages (
    id         SERIAL PRIMARY KEY,
    chat_id    INT NOT NULL REFERENCES chats(id) ON DELETE CASCADE,
    sender_id  INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content    TEXT NOT NULL,
    timestamp  TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 8) RATINGS TABLE
CREATE TABLE ratings (
    id         SERIAL PRIMARY KEY,
    mentor_id  INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    mentee_id  INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    score      INT CHECK (score BETWEEN 1 AND 5),
    comment    TEXT
);

-- 9) Datos de ejemplo
-- Ahora insertamos en users sólo los 5 campos definidos (sin role)
INSERT INTO users (fullname, email, password_hash, university) VALUES
  ('Mario Mentor', 'mentor@example.com', 'hashedpass1', 'University 1'),
  ('Ana Aprendiz', 'mentee@example.com', 'hashedpass2', 'University 1');

INSERT INTO mentors (user_id, subject, intro) VALUES
  (1, 'Algebra I', 'I can help you understand algebra with practical examples.');

INSERT INTO requests (subject, mentee_id, message, status) VALUES
  ('Algebra I', 2, 'Hi, I need help with linear equations.', 'pending');
