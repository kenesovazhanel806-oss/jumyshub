// Мобильді мәзір
const burger = document.querySelector(".burger");
const nav = document.querySelector(".nav");
if (burger && nav) {
  burger.addEventListener("click", () => nav.classList.toggle("open"));
}

// Сақталған вакансиялар (браузер жадында сақталады)
function getSaved() {
  try {
    return JSON.parse(localStorage.getItem("savedJobs")) || [];
  } catch (e) {
    return [];
  }
}

function setSaved(saved) {
  try {
    localStorage.setItem("savedJobs", JSON.stringify(saved));
  } catch (e) {}
}

document.querySelectorAll(".fav-btn").forEach((button) => {
  const id = Number(button.dataset.id);
  button.textContent = getSaved().includes(id) ? "❤️" : "🤍";

  button.addEventListener("click", () => {
    let saved = getSaved();
    saved = saved.includes(id) ? saved.filter((s) => s !== id) : [...saved, id];
    setSaved(saved);
    button.textContent = saved.includes(id) ? "❤️" : "🤍";
  });
});

// Жою алдында растау сұрау (CRUD — Delete)
document.querySelectorAll(".delete-form").forEach((form) => {
  form.addEventListener("submit", (event) => {
    if (!confirm(form.dataset.confirm || "Шынымен жою керек пе?")) {
      event.preventDefault();
    }
  });
});

// Тіркелу: «Компания атауы» өрісі тек жұмыс берушіге көрінеді
const roleSelect = document.querySelector("#role");
const companyGroup = document.querySelector("#company-group");
if (roleSelect && companyGroup) {
  const toggleCompany = () => {
    companyGroup.style.display = roleSelect.value === "employer" ? "block" : "none";
  };
  roleSelect.addEventListener("change", toggleCompany);
  toggleCompany();
}
