INSERT INTO "roles"(id, name, created_at, updated_at) VALUES
  (1, 'some name', '2016-10-20 11:05:00', '2016-10-20 11:05:00');

INSERT INTO "users"(id, name, email, role_id, created_at, updated_at) VALUES
  (1, 'some name', 'some email', 1, '2016-10-20 11:05:00', '2016-10-20 11:05:00');

INSERT INTO "posts"(id, title, body, data, drafted, user_id, posted_at, created_at, updated_at) VALUES
  (1, 'some title', 'some body', '{"title":"1","body":"2"}', 'false', 1, '2022-02-02 12:00:00', '2016-10-20 11:05:00', '2016-10-20 11:05:00');

