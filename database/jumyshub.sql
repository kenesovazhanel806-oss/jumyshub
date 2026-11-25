-- =====================================================
-- JumysHub дерекқоры (MySQL / MariaDB, XAMPP)
-- phpMyAdmin → Import арқылы немесе mysql клиентімен импорттаңыз
-- =====================================================

CREATE DATABASE IF NOT EXISTS jumyshub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE jumyshub;

DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS resumes;
DROP TABLE IF EXISTS vacancies;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS users;

-- Пайдаланушылар (жұмыс іздеуші немесе жұмыс беруші)
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,           -- password_hash() нәтижесі, ашық құпиясөз сақталмайды
  role          ENUM('seeker', 'employer') NOT NULL DEFAULT 'seeker',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Компаниялар (жұмыс берушіге тиесілі)
CREATE TABLE companies (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  name        VARCHAR(150) NOT NULL,
  city        VARCHAR(60)  NOT NULL DEFAULT '',
  description TEXT NULL,
  CONSTRAINT fk_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Салалар
CREATE TABLE categories (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  icon VARCHAR(10) NOT NULL DEFAULT '💼'
) ENGINE=InnoDB;

-- Вакансиялар — CRUD жасалатын негізгі объект
CREATE TABLE vacancies (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id  INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  title       VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  salary      INT UNSIGNED NOT NULL,
  city        VARCHAR(60) NOT NULL,
  work_format ENUM('Кеңседе', 'Гибрид', 'Қашықтан') NOT NULL DEFAULT 'Кеңседе',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_vacancies_company  FOREIGN KEY (company_id)  REFERENCES companies(id)  ON DELETE CASCADE,
  CONSTRAINT fk_vacancies_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
  INDEX idx_vacancies_city (city),
  INDEX idx_vacancies_created (created_at)
) ENGINE=InnoDB;

-- Резюмелер (жұмыс іздеушіге тиесілі)
CREATE TABLE resumes (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL UNIQUE,
  full_name  VARCHAR(100) NOT NULL,
  position   VARCHAR(150) NOT NULL,
  phone      VARCHAR(30)  NOT NULL,
  experience TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_resumes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Өтініштер (резюме ↔ вакансия, көп-көп байланыс)
CREATE TABLE applications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  resume_id  INT UNSIGNED NOT NULL,
  vacancy_id INT UNSIGNED NOT NULL,
  status     ENUM('new', 'viewed', 'accepted', 'rejected') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_application (resume_id, vacancy_id),
  CONSTRAINT fk_applications_resume  FOREIGN KEY (resume_id)  REFERENCES resumes(id)   ON DELETE CASCADE,
  CONSTRAINT fk_applications_vacancy FOREIGN KEY (vacancy_id) REFERENCES vacancies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Мысал деректер
-- Демо аккаунттар (құпиясөзі: demo123):
--   employer@jumyshub.kz — жұмыс беруші
--   seeker@jumyshub.kz   — жұмыс іздеуші
-- =====================================================

INSERT INTO users (id, name, email, password_hash, role) VALUES
  (1, 'Демо жұмыс беруші', 'employer@jumyshub.kz', '$2y$10$lRR3EvldDuspWmCP7SE4/.giCxUETtFjWE2X5V0zAvO9EruqYMp0i', 'employer'),
  (2, 'Демо жұмыс іздеуші', 'seeker@jumyshub.kz',  '$2y$10$lRR3EvldDuspWmCP7SE4/.giCxUETtFjWE2X5V0zAvO9EruqYMp0i', 'seeker');

INSERT INTO categories (id, name, icon) VALUES
  (1, 'IT', '💻'),
  (2, 'Қаржы', '📊'),
  (3, 'Білім беру', '🎓'),
  (4, 'Медицина', '🩺'),
  (5, 'Маркетинг', '📣'),
  (6, 'Басқару', '🗂️');

INSERT INTO companies (id, user_id, name, city, description) VALUES
  (1,  1, 'TechNova',         'Алматы',    'Веб және мобильді қосымшалар әзірлейтін IT компания'),
  (2,  1, 'DataSoft',         'Астана',    'Деректерді өңдеу және автоматтандыру'),
  (3,  1, 'InfoLab',          'Алматы',    'Деректер талдау зертханасы'),
  (4,  1, 'Ақ Жол Сервис',    'Шымкент',   'Бухгалтерлік қызмет көрсету'),
  (5,  1, 'Алтын Капитал',    'Астана',    'Инвестициялық компания'),
  (6,  1, 'Bilim Center',     'Қарағанды', 'Тіл үйрету орталығы'),
  (7,  1, 'EduStep',          'Алматы',    'Онлайн білім беру платформасы'),
  (8,  1, 'Densaulyq Clinic', 'Астана',    'Көпсалалы емхана'),
  (9,  1, 'Shipa Med',        'Алматы',    'Медициналық орталық'),
  (10, 1, 'Brand Media',      'Шымкент',   'Цифрлық маркетинг агенттігі'),
  (11, 1, 'Nomad Trade',      'Қарағанды', 'Сауда компаниясы'),
  (12, 1, 'Team Group',       'Астана',    'Кадрлық консалтинг');

INSERT INTO vacancies (company_id, category_id, title, description, salary, city, work_format, created_at) VALUES
  (1,  1, 'Frontend әзірлеуші',     'HTML, CSS, JavaScript және React білетін әзірлеуші іздейміз. Командада заманауи веб-интерфейстер жасайсыз.', 650000, 'Алматы',    'Гибрид',   NOW() - INTERVAL 20 DAY),
  (2,  1, 'Python бағдарламашысы',  'Python, Django және SQL бойынша тәжірибесі бар маман. Деректерді өңдейтін сервистерді әзірлеу.',             800000, 'Астана',    'Қашықтан', NOW() - INTERVAL 18 DAY),
  (3,  1, 'Деректер талдаушысы',    'Excel, SQL және Power BI арқылы есептер дайындау, бизнес көрсеткіштерін талдау.',                            700000, 'Алматы',    'Кеңседе',  NOW() - INTERVAL 15 DAY),
  (4,  2, 'Бухгалтер',              '1С бағдарламасын білу, салық есептілігін тапсыру, бастапқы құжаттармен жұмыс.',                             350000, 'Шымкент',   'Кеңседе',  NOW() - INTERVAL 12 DAY),
  (5,  2, 'Қаржы талдаушысы',       'Қаржылық модельдер құру, инвестициялық жобаларды бағалау, есептер дайындау.',                               550000, 'Астана',    'Гибрид',   NOW() - INTERVAL 10 DAY),
  (6,  3, 'Ағылшын тілі мұғалімі',  'Балалар мен ересектерге ағылшын тілін оқыту. IELTS сертификаты артықшылық болып саналады.',                  300000, 'Қарағанды', 'Кеңседе',  NOW() - INTERVAL 9 DAY),
  (7,  3, 'Онлайн-курс жетекшісі',  'Онлайн-курстардың мазмұнын жоспарлау, оқытушылармен және студенттермен жұмыс.',                             400000, 'Алматы',    'Қашықтан', NOW() - INTERVAL 7 DAY),
  (8,  4, 'Мейіргер',               'Медициналық білімі бар мейіргер. Пациенттерге күтім жасау, дәрігерге көмектесу.',                           320000, 'Астана',    'Кеңседе',  NOW() - INTERVAL 6 DAY),
  (9,  4, 'Дәрігер-терапевт',       'Пациенттерді қабылдау, диагноз қою және емдеу жоспарын құру. Жұмыс тәжірибесі 2 жылдан.',                   600000, 'Алматы',    'Кеңседе',  NOW() - INTERVAL 5 DAY),
  (10, 5, 'SMM маманы',             'Instagram мен TikTok парақшаларын жүргізу, контент-жоспар құру, жарнама баптау.',                            280000, 'Шымкент',   'Қашықтан', NOW() - INTERVAL 4 DAY),
  (11, 5, 'Маркетолог',             'Нарықты зерттеу, маркетингтік стратегия жасау, жарнамалық науқандарды талдау.',                              450000, 'Қарағанды', 'Гибрид',   NOW() - INTERVAL 3 DAY),
  (12, 6, 'HR менеджері',           'Қызметкерлерді іздеу және іріктеу, сұхбат өткізу, бейімдеу бағдарламасын ұйымдастыру.',                     500000, 'Астана',    'Кеңседе',  NOW() - INTERVAL 1 DAY),
  -- 13-апта: пагинация мен күн сүзгісін көрсету үшін қосымша вакансиялар
  (1,  1, 'Backend әзірлеуші (PHP)', 'PHP 8, MySQL және REST API бойынша тәжірибе. Laravel білу артықшылық.',                                  750000, 'Алматы',    'Гибрид',   NOW() - INTERVAL 2 HOUR),
  (2,  1, 'QA тестілеуші',           'Веб-қосымшаларды қолмен және автоматты тестілеу, тест-кейстер жазу, қателерді тіркеу.',                    420000, 'Астана',    'Қашықтан', NOW() - INTERVAL 2 DAY),
  (3,  1, 'DevOps инженері',         'Docker, CI/CD және Linux серверлерін баптау, жүйенің тұрақты жұмысын қамтамасыз ету.',                     950000, 'Алматы',    'Қашықтан', NOW() - INTERVAL 25 DAY),
  (4,  2, 'Экономист',               'Компанияның бюджетін жоспарлау, шығындарды талдау, ай сайынғы есептер дайындау.',                          380000, 'Шымкент',   'Кеңседе',  NOW() - INTERVAL 35 DAY),
  (6,  3, 'Математика мұғалімі',     'Мектеп оқушыларына математика пәнін оқыту, ҰБТ-ға дайындау.',                                               280000, 'Қарағанды', 'Кеңседе',  NOW() - INTERVAL 40 DAY),
  (9,  4, 'Фармацевт',               'Дәріхана жұмысын ұйымдастыру, клиенттерге дәрі-дәрмек бойынша кеңес беру.',                                 300000, 'Алматы',    'Кеңседе',  NOW() - INTERVAL 45 DAY),
  (10, 5, 'Контент-менеджер',        'Сайт пен әлеуметтік желілерге мәтін жазу, контент жоспарын жүргізу.',                                       260000, 'Шымкент',   'Қашықтан', NOW() - INTERVAL 50 DAY),
  (12, 6, 'Жоба менеджері',          'IT жобаларды жоспарлау, команда жұмысын үйлестіру, мерзімдерді бақылау.',                                   700000, 'Астана',    'Гибрид',   NOW() - INTERVAL 60 DAY);

INSERT INTO resumes (user_id, full_name, position, phone, experience) VALUES
  (2, 'Демо жұмыс іздеуші', 'Junior веб-әзірлеуші', '+7 700 000 00 00', 'HTML, CSS, JavaScript және PHP бойынша оқу жобалары. MySQL дерекқорымен жұмыс тәжірибесі.');
