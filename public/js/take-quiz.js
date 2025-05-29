const user = JSON.parse(localStorage.getItem('user'));
if (!user) {
    window.location.href = "index.html";
}

const API_URL = 'http://localhost/quizProject/api/route.php';
const urlParams = new URLSearchParams(window.location.search);
const quizId = urlParams.get('quiz_id');

if (!quizId) {
    window.location.href = "home.html";
}

// Charger les détails du quiz et les questions
async function loadQuiz() {
    try {
        // Charger les détails du quiz
        const quizRes = await fetch(`${API_URL}?route=get_quiz&id=${quizId}`);
        const quizData = await quizRes.json();
        
        document.getElementById('quiz-title').textContent = quizData.title;
        document.getElementById('quiz-description').textContent = quizData.description;

        // Charger les questions
        const questionsRes = await fetch(`${API_URL}?route=get_questions&quiz_id=${quizId}`);
        const questions = await questionsRes.json();

        const questionsContainer = document.getElementById('questions-container');
        questionsContainer.innerHTML = '';

        questions.forEach((question, index) => {
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-container';
            questionDiv.innerHTML = `
                <h3>Question ${index + 1}</h3>
                <p>${question.question_text}</p>
                <div class="options">
                    ${question.options.map((option, optIndex) => `
                        <label>
                            <input type="radio" 
                                   name="question_${question.id}" 
                                   value="${option}"
                                   required>
                            ${option}
                        </label>
                    `).join('')}
                </div>
            `;
            questionsContainer.appendChild(questionDiv);
        });
    } catch (error) {
        console.error('Erreur lors du chargement du quiz:', error);
        alert('Erreur lors du chargement du quiz');
    }
}

// Soumettre les réponses
async function submitAnswers(event) {
    event.preventDefault();

    const form = document.getElementById('quiz-form');
    const questions = form.querySelectorAll('.question-container');
    const answers = [];

    questions.forEach(questionDiv => {
        const questionId = questionDiv.querySelector('input[type="radio"]').name.split('_')[1];
        const selectedAnswer = questionDiv.querySelector('input[type="radio"]:checked')?.value;

        if (selectedAnswer) {
            answers.push({
                question_id: questionId,
                answer: selectedAnswer
            });
        }
    });

    try {
        const response = await fetch(`${API_URL}?route=submit_answers`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: user.id,
                quiz_id: quizId,
                answers: answers
            })
        });

        const result = await response.json();

        if (result.success) {
            // Afficher les résultats
            document.getElementById('quiz-form').style.display = 'none';
            document.getElementById('result-container').style.display = 'block';
            
            // Afficher le score
            document.getElementById('score-display').innerHTML = `
                <p>Score: ${result.score}/${result.total_questions}</p>
                <p>Pourcentage: ${result.percentage.toFixed(1)}%</p>
            `;

            // Charger et afficher les détails des réponses
            const detailsRes = await fetch(`${API_URL}?route=get_answer_details&user_id=${user.id}&quiz_id=${quizId}`);
            const answerDetails = await detailsRes.json();

            const reviewContainer = document.getElementById('answers-review');
            reviewContainer.innerHTML = '<h3>Détail des réponses:</h3>';

            answerDetails.forEach((detail, index) => {
                const answerDiv = document.createElement('div');
                answerDiv.className = `answer-detail ${detail.is_correct ? 'correct' : 'incorrect'}`;
                answerDiv.innerHTML = `
                    <p><strong>Question ${index + 1}:</strong> ${detail.question_text}</p>
                    <p>Votre réponse: ${detail.user_answer}</p>
                    <p>Réponse correcte: ${detail.correct_answer}</p>
                `;
                reviewContainer.appendChild(answerDiv);
            });
        } else {
            alert(result.error || 'Erreur lors de la soumission des réponses');
        }
    } catch (error) {
        console.error('Erreur lors de la soumission:', error);
        alert('Erreur lors de la soumission des réponses');
    }
}

// Ajouter l'écouteur d'événement pour la soumission du formulaire
document.getElementById('quiz-form').addEventListener('submit', submitAnswers);

// Charger le quiz au chargement de la page
loadQuiz(); 