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
function handleLogin(e) {
  e.preventDefault();

  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;

  fetch('/quizProject/api/route.php?route=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  })
  .then(res => res.json())
  .then(data => {
    document.getElementById('login-message').textContent = data.message || data.error;
    if (data.user) {
      localStorage.setItem('user', JSON.stringify(data.user));
      alert('Connexion réussie. Bienvenue ' + data.user.name + ' !');
      // window.location.href = "quiz.html";
    }
  })
  .catch(err => console.error(err));
}

