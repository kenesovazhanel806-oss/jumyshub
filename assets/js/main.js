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
