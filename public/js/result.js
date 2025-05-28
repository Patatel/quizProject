document.addEventListener("DOMContentLoaded", () => {
  const user = JSON.parse(localStorage.getItem("user"));
  if (!user) return;

  fetch(`http://localhost/quizProject/api/route.php?route=get-user-results&user_id=${user.id}`)
    .then(res => res.json())
    .then(results => {
      const tbody = document.getElementById("results-table-body");
      results.forEach(result => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${result.title}</td>
          <td>${result.score}</td>
          <td>${new Date(result.date_passed).toLocaleString()}</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(err => {
      console.error("Erreur lors du chargement des résultats :", err);
    });
});
