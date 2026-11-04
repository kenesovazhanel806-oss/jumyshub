// ===== Вакансиялар тізімі (мысал деректер, кейін дерекқордан алынады) =====
const jobs = [
  { id: 1, title: "Frontend әзірлеуші", company: "TechNova", city: "Алматы", category: "IT", salary: 650000, format: "Гибрид", posted: 20, color: "#2f5d50" },
  { id: 2, title: "Python бағдарламашысы", company: "DataSoft", city: "Астана", category: "IT", salary: 800000, format: "Қашықтан", posted: 18, color: "#34495e" },
  { id: 3, title: "Деректер талдаушысы", company: "InfoLab", city: "Алматы", category: "IT", salary: 700000, format: "Кеңседе", posted: 15, color: "#3b6ea5" },
  { id: 4, title: "Бухгалтер", company: "Ақ Жол Сервис", city: "Шымкент", category: "Қаржы", salary: 350000, format: "Кеңседе", posted: 12, color: "#8e5a2b" },
  { id: 5, title: "Қаржы талдаушысы", company: "Алтын Капитал", city: "Астана", category: "Қаржы", salary: 550000, format: "Гибрид", posted: 10, color: "#7a6a2b" },
  { id: 6, title: "Ағылшын тілі мұғалімі", company: "Bilim Center", city: "Қарағанды", category: "Білім беру", salary: 300000, format: "Кеңседе", posted: 9, color: "#6a3d5a" },
  { id: 7, title: "Онлайн-курс жетекшісі", company: "EduStep", city: "Алматы", category: "Білім беру", salary: 400000, format: "Қашықтан", posted: 7, color: "#4b4b7a" },
  { id: 8, title: "Мейіргер", company: "Densaulyq Clinic", city: "Астана", category: "Медицина", salary: 320000, format: "Кеңседе", posted: 6, color: "#2e8b57" },
  { id: 9, title: "Дәрігер-терапевт", company: "Shipa Med", city: "Алматы", category: "Медицина", salary: 600000, format: "Кеңседе", posted: 5, color: "#2b4c3f" },
  { id: 10, title: "SMM маманы", company: "Brand Media", city: "Шымкент", category: "Маркетинг", salary: 280000, format: "Қашықтан", posted: 4, color: "#a03c5a" },
  { id: 11, title: "Маркетолог", company: "Nomad Trade", city: "Қарағанды", category: "Маркетинг", salary: 450000, format: "Гибрид", posted: 3, color: "#a0522d" },
  { id: 12, title: "HR менеджері", company: "Team Group", city: "Астана", category: "Басқару", salary: 500000, format: "Кеңседе", posted: 1, color: "#c98d42" }
];

function formatSalary(value) {
  return value.toLocaleString("ru-RU") + " ₸";
}

// ===== Сақталған вакансиялар (браузер жадында сақталады) =====
function getSaved() {
  try {
    return JSON.parse(localStorage.getItem("savedJobs")) || [];
  } catch (e) {
    return [];
  }
}

function toggleSaved(id) {
  let saved = getSaved();
  saved = saved.includes(id) ? saved.filter((s) => s !== id) : [...saved, id];
  try {
    localStorage.setItem("savedJobs", JSON.stringify(saved));
  } catch (e) {}
  return saved.includes(id);
}

// ===== Вакансия карточкасын жасау =====
function createJobCard(job) {
  const isSaved = getSaved().includes(job.id);
  const card = document.createElement("article");
  card.className = "job-card";
  card.innerHTML = `
    <div class="job-cover" style="background:${job.color}">${job.company.charAt(0)}</div>
    <div class="job-info">
      <span class="tag">${job.category}</span>
      <h3>${job.title}</h3>
      <p class="job-author">${job.company} · ${job.city} · ${job.format}</p>
      <div class="job-meta">
        <span class="salary">${formatSalary(job.salary)}</span>
        <button class="fav-btn" title="Вакансияны сақтау">${isSaved ? "❤️" : "🤍"}</button>
      </div>
    </div>`;
  card.querySelector(".fav-btn").addEventListener("click", (e) => {
    e.target.textContent = toggleSaved(job.id) ? "❤️" : "🤍";
  });
  return card;
}

function renderJobs(list, container) {
  container.innerHTML = "";
  list.forEach((job) => container.appendChild(createJobCard(job)));
}

// ===== Мобильді мәзір =====
const burger = document.querySelector(".burger");
const nav = document.querySelector(".nav");
if (burger && nav) {
  burger.addEventListener("click", () => nav.classList.toggle("open"));
}

// ===== Басты бет: жаңа вакансиялар =====
const latestJobs = document.getElementById("latest-jobs");
if (latestJobs) {
  const latest = [...jobs].sort((a, b) => a.posted - b.posted).slice(0, 4);
  renderJobs(latest, latestJobs);
}

// ===== Каталог: іздеу, сүзгі, сұрыптау =====
const catalogJobs = document.getElementById("catalog-jobs");
if (catalogJobs) {
  const searchInput = document.getElementById("search");
  const categorySelect = document.getElementById("category");
  const citySelect = document.getElementById("city");
  const sortSelect = document.getElementById("sort");
  const resultCount = document.getElementById("result-count");
  const emptyText = document.getElementById("empty");

  // Санаттар мен қалалар тізімін толтыру
  function fillSelect(select, values) {
    [...new Set(values)].forEach((value) => {
      const option = document.createElement("option");
      option.value = value;
      option.textContent = value;
      select.appendChild(option);
    });
  }
  fillSelect(categorySelect, jobs.map((j) => j.category));
  fillSelect(citySelect, jobs.map((j) => j.city));

  // Басты беттен келген іздеу сөзі
  const params = new URLSearchParams(window.location.search);
  if (params.get("q")) searchInput.value = params.get("q");
  if (params.get("category")) categorySelect.value = params.get("category");

  function applyFilters() {
    const query = searchInput.value.trim().toLowerCase();
    const category = categorySelect.value;
    const city = citySelect.value;

    let result = jobs.filter((j) => {
      const matchText = j.title.toLowerCase().includes(query) || j.company.toLowerCase().includes(query);
      const matchCategory = category === "all" || j.category === category;
      const matchCity = city === "all" || j.city === city;
      return matchText && matchCategory && matchCity;
    });

    if (sortSelect.value === "new") result.sort((a, b) => a.posted - b.posted);
    if (sortSelect.value === "salary") result.sort((a, b) => b.salary - a.salary);
    if (sortSelect.value === "title") result.sort((a, b) => a.title.localeCompare(b.title, "kk"));

    renderJobs(result, catalogJobs);
    resultCount.textContent = `Табылды: ${result.length} вакансия`;
    emptyText.style.display = result.length ? "none" : "block";
  }

  searchInput.addEventListener("input", applyFilters);
  categorySelect.addEventListener("change", applyFilters);
  citySelect.addEventListener("change", applyFilters);
  sortSelect.addEventListener("change", applyFilters);
  applyFilters();
}

// ===== Форманы тексеру =====
function showError(input, text) {
  input.classList.toggle("invalid", Boolean(text));
  input.parentElement.querySelector(".error-text").textContent = text;
  return !text;
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Кіру формасы
const loginForm = document.getElementById("login-form");
if (loginForm) {
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const { email, password } = loginForm.elements;
    const message = document.getElementById("form-message");

    const ok = [
      showError(email, isValidEmail(email.value) ? "" : "Дұрыс email енгізіңіз"),
      showError(password, password.value.length >= 6 ? "" : "Құпиясөз кемінде 6 таңбадан тұруы керек")
    ].every(Boolean);

    message.className = "message " + (ok ? "success" : "error");
    message.textContent = ok ? "Сіз жүйеге сәтті кірдіңіз!" : "Қателерді түзетіңіз";
  });
}

// Тіркелу формасы
const registerForm = document.getElementById("register-form");
if (registerForm) {
  registerForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const { name, email, password, confirm } = registerForm.elements;
    const message = document.getElementById("form-message");

    const ok = [
      showError(name, name.value.trim().length >= 2 ? "" : "Атыңызды енгізіңіз"),
      showError(email, isValidEmail(email.value) ? "" : "Дұрыс email енгізіңіз"),
      showError(password, password.value.length >= 6 ? "" : "Құпиясөз кемінде 6 таңбадан тұруы керек"),
      showError(confirm, confirm.value === password.value && confirm.value ? "" : "Құпиясөздер сәйкес келмейді")
    ].every(Boolean);

    message.className = "message " + (ok ? "success" : "error");
    message.textContent = ok ? `Құттықтаймыз, ${name.value}! Тіркелу сәтті өтті.` : "Қателерді түзетіңіз";
    if (ok) registerForm.reset();
  });
}

// Резюме формасы
const resumeForm = document.getElementById("resume-form");
if (resumeForm) {
  resumeForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const { fullname, position, phone, experience } = resumeForm.elements;
    const message = document.getElementById("form-message");

    const ok = [
      showError(fullname, fullname.value.trim().length >= 2 ? "" : "Аты-жөніңізді енгізіңіз"),
      showError(position, position.value.trim() ? "" : "Қалаған лауазымды енгізіңіз"),
      showError(phone, /^\+?[\d\s()-]{10,}$/.test(phone.value) ? "" : "Дұрыс телефон нөмірін енгізіңіз"),
      showError(experience, experience.value.trim().length >= 10 ? "" : "Тәжірибеңізді қысқаша жазыңыз")
    ].every(Boolean);

    message.className = "message " + (ok ? "success" : "error");
    message.textContent = ok ? "Резюме сақталды!" : "Қателерді түзетіңіз";

    if (ok) {
      const resume = {
        fullname: fullname.value,
        position: position.value,
        phone: phone.value,
        experience: experience.value
      };
      try {
        localStorage.setItem("resume", JSON.stringify(resume));
      } catch (err) {}
    }
  });
}
