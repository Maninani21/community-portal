<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['admin_id'])){
    header('Location: login.php');
    exit;
}

// Fetch analytics data
$postsData = $pdo->query("
    SELECT p.id, p.title, COUNT(c.id) AS comments, COUNT(a.id) AS views
    FROM posts p
    LEFT JOIN comments c ON p.id=c.post_id
    LEFT JOIN analytics a ON p.id=a.post_id
    GROUP BY p.id
    ORDER BY views DESC
")->fetchAll();

$topUsers = $pdo->query("
    SELECT u.name, COUNT(p.id) AS posts_count
    FROM users u
    LEFT JOIN posts p ON u.id=p.author_id
    GROUP BY u.id
    ORDER BY posts_count DESC
    LIMIT 5
")->fetchAll();

$totalViews = $pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();
$totalComments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Prepare data for charts
$postTitles = [];
$postViews = [];
$postComments = [];
foreach($postsData as $p){
    $postTitles[] = sanitize($p['title']);
    $postViews[] = (int)$p['views'];
    $postComments[] = (int)$p['comments'];
}

$topUserNames = [];
$topUserPosts = [];
foreach($topUsers as $u){
    $topUserNames[] = sanitize($u['name']);
    $topUserPosts[] = (int)$u['posts_count'];
}
?>

<?php include 'header.php'; ?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Analytics Dashboard</h1>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-eye-fill fs-1 me-3"></i>
                    <div>
                        <h6>Total Views</h6>
                        <p class="fs-3"><?= $totalViews ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-chat-left-text-fill fs-1 me-3"></i>
                    <div>
                        <h6>Total Comments</h6>
                        <p class="fs-3"><?= $totalComments ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphical Analytics -->
    <div class="row g-4 mb-4">
        <div class="col-md-12 overflow-auto">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">Post Views & Comments</div>
                <div class="card-body">
                    <canvas id="postViewsCommentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">Top 5 Active Users</div>
                <div class="card-body">
                    <canvas id="topUsersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Tables -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Posts Analytics (Manual)</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Views</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($postsData as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= sanitize($p['title']) ?></td>
                            <td><?= $p['views'] ?></td>
                            <td><?= $p['comments'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Top 5 Active Users (Manual)</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>User Name</th>
                        <th>Posts Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($topUsers as $u): ?>
                        <tr>
                            <td><?= sanitize($u['name']) ?></td>
                            <td><?= $u['posts_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
const postTitles = <?= json_encode($postTitles) ?>;
const postViews = <?= json_encode($postViews) ?>;
const postComments = <?= json_encode($postComments) ?>;
const topUserNames = <?= json_encode($topUserNames) ?>;
const topUserPosts = <?= json_encode($topUserPosts) ?>;

// Post Views & Comments Chart
new Chart(document.getElementById('postViewsCommentsChart'), {
    type: 'bar',
    data: {
        labels: postTitles,
        datasets: [
            {
                label: 'Views',
                data: postViews,
                backgroundColor: 'rgba(13,110,253,0.7)',
                borderColor: '#0d6efd',
                borderWidth: 1
            },
            {
                label: 'Comments',
                data: postComments,
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderColor: '#198754',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            datalabels: {
                color: 'white',
                anchor: 'end',
                align: 'start',
                font: { weight: 'bold' }
            },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            x: { stacked: false },
            y: { beginAtZero: true }
        }
    },
    plugins: [ChartDataLabels]
});

// Top Users Chart
new Chart(document.getElementById('topUsersChart'), {
    type: 'doughnut',
    data: {
        labels: topUserNames,
        datasets: [{
            label: 'Posts Count',
            data: topUserPosts,
            backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6c757d'],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            datalabels: {
                color: '#000',
                formatter: (value, context) => value
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>

<?php include 'footer.php'; ?>
