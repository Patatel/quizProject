function showForm(type) {
  document.getElementById('login-form').classList.add('hidden');
  document.getElementById('register-form').classList.add('hidden');
  document.getElementById(type + '-form').classList.remove('hidden');
}

// Envoie la requête d'inscription
function handleRegister(e) {
  e.preventDefault();

  const name = document.getElementById('register-name').value;
  const email = document.getElementById('register-email').value;
  const password = document.getElementById('register-password').value;

  fetch('/quizProject/api/route.php?route=register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name, email, password })
  })
  .then(res => res.json())
  .then(data => {
    const messageElement = document.getElementById('register-message');
    if (data.message) {
      messageElement.textContent = data.message;
      messageElement.style.color = 'green';
      showForm('login');
    } else if (data.error) {
      messageElement.textContent = data.error;
      messageElement.style.color = 'red';
    }
  })
  .catch(err => console.error(err));
}

// Envoie la requête de connexion
function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;

  fetch('/quizProject/api/route.php?route=login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email, password })
  })
  .then(response => response.json())
  .then(data => {
    if (data.user) {
      // Stocker les données de l'utilisateur dans le localStorage
      localStorage.setItem('user', JSON.stringify(data.user));
      // Rediriger vers la page home
      window.location.href = '/quizProject/public/html/home.html';
    } else {
      document.getElementById('login-message').textContent = data.error || "Erreur inconnue";
    }
  })
  .catch(error => {
    console.error("Erreur lors de la connexion :", error);
    document.getElementById('login-message').textContent = "Erreur réseau ou serveur.";
  });
}
