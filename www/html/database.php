<?php
$servername = "localhost";
$username = "quizapp";
$password = "quizpass";
$dbname = "quiz_lab";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT
)");

$conn->query("CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
)");

$result = $conn->query("SELECT COUNT(*) AS cnt FROM quizzes");
$row = $result->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("INSERT INTO quizzes (title, description) VALUES ('General Knowledge', 'Test your general knowledge with these fun facts!')");
    $conn->query("INSERT INTO quizzes (title, description) VALUES ('PHP Basics', 'How well do you know PHP fundamentals?')");

    $conn->query("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
        (1, 'What is the capital of France?', 'London', 'Paris', 'Berlin', 'Madrid', 'B'),
        (1, 'How many continents are there?', '5', '6', '7', '8', 'C'),
        (1, 'What is the largest ocean?', 'Atlantic', 'Indian', 'Arctic', 'Pacific', 'D'),
        (1, 'Which planet is known as the Red Planet?', 'Venus', 'Jupiter', 'Mars', 'Saturn', 'C'),
        (1, 'What is the chemical symbol for water?', 'H2O', 'CO2', 'NaCl', 'O2', 'A'),
        (2, 'What does PHP stand for?', 'Personal Home Page', 'PHP: Hypertext Preprocessor', 'Private Host Protocol', 'Public HTML Parser', 'B'),
        (2, 'Which symbol is used for variable names in PHP?', '@', '#', '$', '&', 'C'),
        (2, 'Which function is used to print output in PHP?', 'print()', 'echo', 'write()', 'Both A and B', 'D'),
        (2, 'How do you start a PHP block?', '<?php', '<script>', '<?', 'Both A and C', 'D'),
        (2, 'Which superglobal holds form data sent via POST?', '$_GET', '$_POST', '$_REQUEST', '$_SERVER', 'B')");
}
?>
