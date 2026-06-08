<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$quizzes = $conn->query("SELECT id, title, description FROM quizzes");

$results = $conn->prepare("SELECT quiz_id, score, total, completed_at FROM results WHERE user_id = ? ORDER BY completed_at DESC");
$results->bind_param("i", $_SESSION['user_id']);
$results->execute();
$results_result = $results->get_result();
$user_results = [];
while ($row = $results_result->fetch_assoc()) {
    $user_results[$row['quiz_id']] = $row;
}
$results->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; }
        body { background: #f5f7fb; min-height: 100vh; }
        .navbar { background: white; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 20px; color: #333; }
        .navbar .user-info { display: flex; align-items: center; gap: 12px; }
        .navbar .username { color: #667eea; font-weight: 600; }
        .navbar a { color: #888; text-decoration: none; font-size: 14px; }
        .navbar a:hover { color: #c33; }
        .container { max-width: 900px; margin: 0 auto; padding: 32px 16px; }
        h2 { color: #333; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        .card h3 { color: #333; margin-bottom: 8px; }
        .card p { color: #888; font-size: 14px; margin-bottom: 16px; line-height: 1.5; }
        .card .meta { font-size: 13px; color: #aaa; margin-bottom: 16px; }
        .card .btn { display: inline-block; padding: 10px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; transition: opacity 0.2s; }
        .card .btn:hover { opacity: 0.9; }
        .card .score-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .score-good { background: #e8f5e9; color: #2e7d32; }
        .score-medium { background: #fff3e0; color: #e65100; }
        .score-bad { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Quiz Platform</h1>
        <div class="user-info">
            <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php">Sign out</a>
        </div>
    </div>
    <div class="container">
        <h2>Available Quizzes</h2>
        <div class="grid">
            <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                <?php
                $qid = $quiz['id'];
                $attempted = isset($user_results[$qid]);
                ?>
                <div class="card">
                    <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                    <p><?= htmlspecialchars($quiz['description']) ?></p>
                    <?php if ($attempted): ?>
                        <?php
                        $r = $user_results[$qid];
                        $pct = $r['total'] > 0 ? round($r['score'] / $r['total'] * 100) : 0;
                        $cls = $pct >= 70 ? 'score-good' : ($pct >= 40 ? 'score-medium' : 'score-bad');
                        ?>
                        <div class="meta">Last attempt: <?= date('M j, Y', strtotime($r['completed_at'])) ?></div>
                        <span class="score-badge <?= $cls ?>"><?= $r['score'] ?>/<?= $r['total'] ?> (<?= $pct ?>%)</span>
                        <a href="quiz.php?id=<?= $qid ?>" class="btn" style="margin-left: 8px;">Retake</a>
                    <?php else: ?>
                        <a href="quiz.php?id=<?= $qid ?>" class="btn">Start Quiz</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
