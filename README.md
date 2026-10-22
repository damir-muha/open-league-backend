# Open League Engine

<p align="center">
  <strong>High-performance, open-source Sports League & Tournament Management Platform</strong><br>
  Built with Laravel, Filament 3 Admin, PostgreSQL, and RoadRunner / Octane.
</p>

<p align="center">
  <a href="#english">English</a> •
  <a href="#русский">Русский</a> •
  <a href="#қазақша">Қазақша</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/License-MIT--0-blue.svg" alt="License MIT-0">
  <img src="https://img.shields.io/badge/PHP-8.2+-purple.svg" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-11%20%2F%2012-red.svg" alt="Laravel">
  <img src="https://img.shields.io/badge/Filament-v3-orange.svg" alt="Filament 3">
  <img src="https://img.shields.io/badge/Database-PostgreSQL-336791.svg" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Server-RoadRunner%20%2F%20Octane-blueviolet.svg" alt="RoadRunner">
</p>

---

<a name="english"></a>
## English

### Overview

**Open League Engine** is an enterprise-grade, extensible backend platform designed for organizing, managing, and broadcasting sports tournaments of any scale (football, futsal, basketball, e-sports, and more).

Whether you are launching an amateur city league, a youth academy championship, or a multi-division national tournament, this engine handles the entire competition lifecycle:
- Full-featured **Filament 3 admin panel** for league administrators, referees, and editors.
- High-throughput **REST API** powering modern web applications and mobile apps (iOS & Android).
- Production-grade **Docker** environment with **RoadRunner / Octane** for lightning-fast response times under heavy game-day traffic.

---

### Key Features

#### 1. Flexible Competition Architecture
- **Multi-tournament hierarchy:** Manage multiple leagues, cups, and divisions simultaneously.
- **Versatile tournament formats:** Single/double round-robin league tables, group stages with advancement rules, and knockout playoff brackets.
- **Seasons & Stages:** Archive past seasons, manage current seasons, and configure multiple stages per season.

#### 2. Match Management & Live Events
- **Match scheduling:** Venues, kickoff datetimes, pitch allocations, and referee assignments.
- **Live event logging:** Real-time recording of goals, assists, yellow cards, red cards, own goals, and substitutions.
- **Penalty shootouts & overtime:** Native support for cup matches requiring tie-breakers.
- **Match rosters & lineups:** Starting lineups, substitutes, coaching staff, and jersey numbers.

#### 3. Automated Standings & Disciplinary System
- **Real-time league tables:** Automatic calculation of games played, wins, draws, losses, goals for/against, goal difference, and points.
- **Head-to-head & tie-break criteria:** Supports custom sorting and point adjustments.
- **Disciplinary tracking:** Automated tracking of accumulated yellow cards, direct red cards, and automated player suspensions across rounds.
- **Point deductions:** Manual points deduction system with documented legal rationale.

#### 4. Team & Player Ecosystem
- **Player profiles:** Biographical records, photo galleries, career statistics, and registration identifiers.
- **Team roster management:** Seasonal rosters, player numbers, and active/inactive roster statuses.
- **Transfer windows & requests:** Regulated player transfers between clubs with validation.

#### 5. Content & Media Management
- **News & categories:** Built-in rich-text CMS for match reports, interviews, and official announcements.
- **Media galleries:** Photo galleries and YouTube / video highlights linked directly to matches.
- **Sponsorship & ads:** Banner placement management for tournament and team sponsors.

#### 6. Security & Role-Based Access Control (RBAC)
- Powered by **Spatie Laravel Permission** and **Filament Shield**.
- Pre-configured roles: Super Admin, League Manager, Referee / Match Official, Content Editor.

---

### Tech Stack

- **Framework:** Laravel 11 / 12
- **Admin Panel:** Filament 3 (TALL Stack)
- **High Performance Runtime:** Laravel Octane with Spiral RoadRunner
- **Database:** PostgreSQL 15+ (with support for MySQL 8+)
- **Authentication:** Laravel Sanctum
- **Containerization:** Docker & Docker Compose

---

### Quick Start (Docker)

The fastest way to get up and running is using Docker Compose:

```bash
# 1. Clone the repository
git clone https://github.com/damir-muha/open-league-backend.git
cd open-league-backend

# 2. Copy the environment template
cp .env.example .env

# 3. Start containers (app, postgres, pgadmin)
docker compose up -d --build

# 4. Generate application key
docker compose exec api-app php artisan key:generate

# 5. Run database migrations & seed initial permissions and admin user
docker compose exec api-app php artisan migrate --seed

# 6. (Optional) Seed demo tournament data
docker compose exec api-app php artisan db:seed --class=TestDataSeeder
```

The application will be accessible at:
- **API & App:** `http://localhost:8000`
- **Admin Panel:** `http://localhost:8000/admin`
  - Default Admin: `admin@admin.com`
  - Default Password: `password`
- **PgAdmin:** `http://localhost:8080` (admin@example.com / admin)

---

### Manual Local Setup (Without Docker)

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# Configure DB credentials in .env, then:
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

---

### Author & Origins

**Created and originally engineered by Damir Mukhamedkali.**

The initial architecture, data models, business workflows, and API specifications were designed and built to power real-world, high-stakes sports competitions. This codebase is released to the global developer community to serve as a robust, libre foundation for sports software worldwide.

---

### License

This software is released under the **MIT-0 (MIT No Attribution)** license.

You are free to use, copy, modify, distribute, publish, and even sell commercial solutions or private derivatives based on this codebase, **without any requirement to retain or cite the author's name in your downstream distributions**. See [LICENSE](LICENSE) for details.

---

### Support & Contributions

Pull requests, discussions, and feature proposals are warmly welcome. If you find this engine valuable for your league or business, feel free to star the repository and consider sponsoring development.

---

<a name="русский"></a>
## Русский

### О проекте

**Open League Engine** — это открытая, высокопроизводительная платформа для комплексной организации, судейства и освещения спортивных турниров и чемпионатов (футбол, футзал, баскетбол и другие командные виды спорта).

Движок разработан для того, чтобы автоматизировать всю рутину спортивной лиги: от регистрации команд и заявки игроков до онлайн-протоколов матчей, автоматических дисквалификаций и мгновенного пересчета турнирных таблиц.

---

### Основные возможности

* **Турнирная структура:** Поддержка круговых чемпионатов, групповых этапов с плей-офф, кубковых стадий и стыковых матчей.
* **Протоколы матчей в реальном времени:** Фиксация голов, ассистов, желтых и красных карточек, замен, автоголов и послематчевых пенальти.
* **Автоматические таблицы и статистика:** Мгновенный расчет очков, разницы мячей, личных встреч, а также учет дисциплинарных штрафов (снятие очков).
* **Умная система дисквалификаций:** Автоматический подсчет перебора желтых карточек, прямых удалений и расчет количества матчей бана игрока.
* **Кабинет игроков и команд:** Профили, заявочные листы на сезон, номера, трансферные окна и переходы.
* **Административная панель Filament 3:** Удобный интерфейс с гибким разграничением прав (супер-администраторы, секретари матчей, судьи, контент-менеджеры).
* **Высокоскоростной REST API:** Готовый бэкенд для подключения веб-сайтов и мобильных приложений (iOS / Android / Flutter).
* **Готовность к высоким нагрузкам:** Docker-окружение с **RoadRunner / Octane** и PostgreSQL, обеспечивающее стабильную работу в дни массовых игр.

---

### Быстрый старт через Docker

```bash
# 1. Клонируйте репозиторий
git clone https://github.com/damir-muha/open-league-backend.git
cd open-league-backend

# 2. Создайте файл конфигурации
cp .env.example .env

# 3. Запустите контейнеры
docker compose up -d --build

# 4. Сгенерируйте ключ приложения
docker compose exec api-app php artisan key:generate

# 5. Примените миграции и сидеры ролей
docker compose exec api-app php artisan migrate --seed

# 6. (По желанию) Загрузите демонстрационные данные
docker compose exec api-app php artisan db:seed --class=TestDataSeeder
```

Вход в систему:
- **Админ-панель:** `http://localhost:8000/admin`
  - Email: `admin@admin.com`
  - Пароль: `password`

---

### Авторство

**Создатель и автор архитектуры: Дамир Мухамедкали (Damir Mukhamedkali).**

Проект создан на основе реального опыта запуска спортивных соревнований и передан в открытый доступ как свободный фундамент для спортивного сообщества и разработчиков.

---

### Лицензия

Проект распространяется под лицензией **MIT-0 (MIT No Attribution)**.

Вы имеете право свободно использовать, копировать, модифицировать, объединять с коммерческими продуктами и продавать решения на базе этого кода. При этом вы **не обязаны** указывать имя автора или сохранять лицензионные уведомления в своих производных продуктах. Подробнее в файле [LICENSE](LICENSE).

---

<a name="қазақша"></a>
## Қазақша

### Жоба туралы

**Open League Engine** — бұл спорттық турнирлерді, чемпионаттар мен лигаларды (футбол, футзал, баскетбол және басқа да командалық спорт түрлері) ұйымдастыруға, басқаруға және автоматтандыруға арналған ашық әрі қуатты жүйе.

Бұл платформа әуесқой қалалық лигалардан бастап, кәсіби жарыстарға дейінгі барлық процестерді толық қамтиды: ойыншыларды тіркеу, маусымдық өтінімдер, онлайн матч хаттамалары, автоматты кестелер мен мобильді қосымшаларға арналған дайын API.

---

### Негізгі мүмкіндіктері

* **Икемді жарыс құрылымы:** Айналымдық лигалар, топтық кезеңдер, плей-офф (кубоктық жүйе) және стык матчтары.
* **Тікелей эфирдегі матч хаттамасы:** Голдар, нәтижелі пастар, сары/қызыл қағаздар, ойыншы алмасулары және пенальти сериялары.
* **Автоматты турнирлік кестелер:** Ұпайларды, доп айырмасын, өзара кездесулерді автоматты түрде есептеу және тәртіптік ұпай шегерулері.
* **Дисквалификация жүйесі:** Сары қағаздардың жиналуын және қызыл қағаз алған ойыншылардың өткізіп алатын ойындарын автоматты бақылау.
* **Командалар мен ойыншылар базасы:** Жеке профильдер, фотолар, маусымдық құрамдар, нөмірлер және трансферлік терезелер.
* **Filament 3 әкімшілік панелі:** Рөлдер мен құқықтарды бөлу (Бас әкімші, лига менеджері, матч төрешісі, модератор).
* **Жоғары жылдамдықты REST API:** Веб-сайттар мен мобильді қосымшаларға (Flutter / React / iOS / Android) деректерді жедел жеткізу.
* **Жоғары жүктемеге төзімділік:** **RoadRunner / Octane**, PostgreSQL және Docker негізінде жасалған заманауи архитектура.

---

### Docker арқылы жылдам іске қосу

```bash
# 1. Репозиторийді көшіріп алыңыз
git clone https://github.com/damir-muha/open-league-backend.git
cd open-league-backend

# 2. Баптаулар файлын жасаңыз
cp .env.example .env

# 3. Контейнерлерді іске қосыңыз
docker compose up -d --build

# 4. Қосымша кілтін генерациялаңыз
docker compose exec api-app php artisan key:generate

# 5. Мәліметтер қорын дайындап, бастапқы рөлдер мен әкімшіні енгізіңіз
docker compose exec api-app php artisan migrate --seed

# 6. (Қалауыңыз бойынша) Сынақ мәліметтерін қосыңыз
docker compose exec api-app php artisan db:seed --class=TestDataSeeder
```

Кіру сілтемелері:
- **Әкімшілік панель:** `http://localhost:8000/admin`
  - Электрондық пошта: `admin@admin.com`
  - Құпия сөз: `password`

---

### Жоба авторы

**Архитектура авторы және жасаушысы: Дамир Мұхамедқали (Damir Mukhamedkali).**

Бұл жоба шынайы спорттық лигалардың тәжірибесіне сүйене отырып жасалған және әлемдік бағдарламашылар қауымдастығына спорт саласындағы кез келген жобаларды дамыту үшін еркін негіз ретінде ұсынылады.

---

### Лицензия

Жоба **MIT-0 (MIT No Attribution)** еркін лицензиясымен жарияланған.

Кез келген адам немесе компания бұл кодты еркін пайдалануға, өзгертуге, коммерциялық өнімдерге қосуға және сатуға құқылы. Сонымен қатар, өз өнімдеріңізде автордың атын сақтау міндетті **емес**. Толығырақ [LICENSE](LICENSE) файлында.
