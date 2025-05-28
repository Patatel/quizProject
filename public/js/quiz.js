const user = JSON.parse(localStorage.getItem('user'));
if (!user) {
  window.location.href = "index.html"; // redirection si non connecté
}

const API_URL = 'http://localhost/quizProject/api/route.php';

async function loadQuizzes() {
  const res = await fetch(`${API_URL}?route=my_quizzes&user_id=${user.id}`);
  const data = await res.json();

  const container = document.getElementById('quizzes-container');
  container.innerHTML = '';

  if (Array.isArray(data)) {
    data.forEach(quiz => {
      const li = document.createElement('li');
      li.innerHTML = `
        <strong>${quiz.title}</strong> - ${quiz.description}
        <button onclick="editQuiz(${quiz.id})">Modifier</button>
        <button onclick="deleteQuiz(${quiz.id})">Supprimer</button>
      `;
      container.appendChild(li);
    });
  } else {
    container.innerHTML = "<li>Aucun quiz pour le moment.</li>";
  }
}

async function createQuiz(event) {
  event.preventDefault();
  const title = document.getElementById('quiz-title').value;
  const description = document.getElementById('quiz-description').value;

  const res = await fetch(`${API_URL}?route=create_quiz`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title, description, user_id: user.id })
  });

  const result = await res.json();
  alert(result.message || result.error);
  if (data.success) {
    window.location.href = "http://localhost/quizProject/public/html/quiz.html?success=1";
  }
  loadQuizzes();
}

async function deleteQuiz(id) {
  if (!confirm("Confirmer la suppression ?")) return;

  const res = await fetch(`${API_URL}?route=delete_quiz&id=${id}`, {
    method: 'DELETE'
  });
  const result = await res.json();
  alert(result.message || result.error);
  loadQuizzes();
}

function logout() {
  localStorage.removeItem('user');
  window.location.href = "/../public/index.html";
}

loadQuizzes();
