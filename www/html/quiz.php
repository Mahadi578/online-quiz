<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$quiz_id = (int)$_GET['id'];

$quiz_result = $conn->prepare("SELECT id, title FROM quizzes WHERE id = ?");
$quiz_result->bind_param("i", $quiz_id);
$quiz_result->execute();
$quiz = $quiz_result->get_result()->fetch_assoc();
$quiz_result->close();

if (!$quiz) {
    header('Location: dashboard.php');
    exit;
}

$questions_result = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option FROM questions WHERE quiz_id = ?");
$questions_result->bind_param("i", $quiz_id);
$questions_result->execute();
$questions = $questions_result->get_result()->fetch_all(MYSQLI_ASSOC);
$questions_result->close();

$score = null;
$total = count($questions);
$answers = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $score = 0;
    foreach ($questions as $q) {
        $qid = $q['id'];
        $user_answer = $_POST["q_$qid"] ?? '';
        $answers[$qid] = $user_answer;
        if ($user_answer === $q['correct_option']) {
            $score++;
        }
    }
    $stmt = $conn->prepare("INSERT INTO results (user_id, quiz_id, score, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $quiz_id, $score, $total);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> - Quiz Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; }
        body { background: #f5f7fb; min-height: 100vh; }
        .navbar { background: white; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 20px; color: #333; }
        .navbar a { color: #667eea; text-decoration: none; font-size: 14px; font-weight: 500; }
        .container { max-width: 800px; margin: 0 auto; padding: 32px 16px; }
        h2 { color: #333; margin-bottom: 8px; }
        .question-count { color: #888; font-size: 14px; margin-bottom: 24px; }
        .question { background: white; border-radius: 12px; padding: 24px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .question h3 { font-size: 16px; color: #333; margin-bottom: 16px; }
        .question .q-number { color: #667eea; font-weight: 700; margin-right: 8px; }
        .options { display: flex; flex-direction: column; gap: 10px; }
        .options label { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.2s; font-size: 15px; }
        .options label:hover { border-color: #667eea; background: #f8f9ff; }
        .options input[type="radio"] { accent-color: #667eea; }
        .options label.correct { border-color: #2e7d32; background: #e8f5e9; }
        .options label.incorrect { border-color: #c62828; background: #ffebee; }
        .options label .indicator { margin-left: auto; font-weight: 600; font-size: 16px; }
        .btn { padding: 14px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-secondary { background: #e8eaf6; color: #667eea; }
        .actions { text-align: center; margin-top: 24px; }
        .result-card { background: white; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 500px; margin: 40px auto; }
        .result-card .score { font-size: 64px; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .result-card .label { color: #888; font-size: 18px; margin-top: 8px; }
        .result-card .pct { font-size: 24px; color: #555; margin-top: 8px; }
        .result-card .feedback { margin-top: 16px; font-size: 16px; }
        .result-actions { margin-top: 24px; display: flex; gap: 12px; justify-content: center; }
        .result-actions a { padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
    <div class="container">
        <?php if ($score !== null): ?>
            <?php
            $pct = $total > 0 ? round($score / $total * 100) : 0;
            if ($pct >= 70) {
                $feedback = "Great job! You know your stuff!";
                $fb_cls = "color: #2e7d32;";
            } elseif ($pct >= 40) {
                $feedback = "Not bad! A little more practice and you'll ace it.";
                $fb_cls = "color: #e65100;";
            } else {
                $feedback = "Keep studying! You'll do better next time.";
                $fb_cls = "color: #c62828;";
            }
            ?>
            <div class="result-card">
                <div class="score"><?= $score ?>/<?= $total ?></div>
                <div class="pct"><?= $pct ?>%</div>
                <div class="feedback" style="<?= $fb_cls ?>"><?= $feedback ?></div>
                <div class="result-actions">
                    <a href="quiz.php?id=<?= $quiz_id ?>" class="btn" style="color:white;">Retake</a>
                    <a href="dashboard.php" class="btn btn-secondary" style="background:#e8eaf6;color:#667eea;">Dashboard</a>
                </div>
            </div>
            <h2 style="margin-top: 32px;">Review Answers</h2>
            <?php foreach ($questions as $i => $q): ?>
                <?php
                $qid = $q['id'];
                $ua = $answers[$qid] ?? '';
                ?>
                <div class="question">
                    <h3><span class="q-number"><?= $i + 1 ?>.</span> <?= htmlspecialchars($q['question_text']) ?></h3>
                    <div class="options">
                        <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                            <?php
                            $opt_text = $q["option_" . strtolower($opt)];
                            $cls = '';
                            $indicator = '';
                            if ($opt === $q['correct_option']) {
                                $cls = 'correct';
                                $indicator = $ua === $opt ? '✓' : '✓';
                            } elseif ($ua === $opt && $opt !== $q['correct_option']) {
                                $cls = 'incorrect';
                                $indicator = '✗';
                            }
                            ?>
                            <label class="<?= trim($cls) ?>">
                                <input type="radio" disabled <?= $ua === $opt ? 'checked' : '' ?>>
                                <span><?= $opt ?>. <?= htmlspecialchars($opt_text) ?></span>
                                <?php if ($indicator): ?>
                                    <span class="indicator"><?= $indicator ?></span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <h2><?= htmlspecialchars($quiz['title']) ?></h2>
            <p class="question-count"><?= $total ?> questions</p>
            <form method="POST">
                <?php foreach ($questions as $i => $q): ?>
                    <div class="question">
                        <h3><span class="q-number"><?= $i + 1 ?>.</span> <?= htmlspecialchars($q['question_text']) ?></h3>
                        <div class="options">
                            <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                <?php $opt_text = $q["option_" . strtolower($opt)]; ?>
                                <label>
                                    <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $opt ?>" required>
                                    <span><?= $opt ?>. <?= htmlspecialchars($opt_text) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="actions">
                    <button type="submit" name="submit_quiz" class="btn">Submit Answers</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
