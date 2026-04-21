<?php
/**
 * Admin Dashboard
 * Central hub for the administrator with:
 * - Statistics overview cards
 * - Chart.js analytics (pie chart + bar chart)
 * - Nairobi Image Map for location-based filtering
 * - Recent reports table
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// ── Statistics ───────────────────────────────────────────────
$stats = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned,
    SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived
    FROM reports")->fetch();

$totalUsers = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$totalFeedback = $pdo->query("SELECT COUNT(*) as count FROM feedback")->fetch()['count'];
$avgRating = $pdo->query("SELECT COALESCE(AVG(rating), 0) as avg FROM feedback")->fetch()['avg'];

// ── Location distribution for charts ─────────────────────────
$locationData = $pdo->query("SELECT location, COUNT(*) as count FROM reports GROUP BY location ORDER BY count DESC")->fetchAll();
$locationLabels = json_encode(array_column($locationData, 'location'));
$locationCounts = json_encode(array_column($locationData, 'count'));

// ── Monthly trends (last 6 months) ──────────────────────────
$trendData = $pdo->query("SELECT DATE_FORMAT(date_submitted, '%Y-%m') as month, COUNT(*) as count 
                          FROM reports 
                          WHERE date_submitted >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(date_submitted, '%Y-%m') 
                          ORDER BY month ASC")->fetchAll();
$trendLabels = json_encode(array_column($trendData, 'month'));
$trendCounts = json_encode(array_column($trendData, 'count'));

// ── Recent reports with user info ────────────────────────────
$recentReports = $pdo->query("SELECT r.*, u.name as user_name 
                              FROM reports r 
                              JOIN users u ON r.user_id = u.id 
                              ORDER BY r.date_submitted DESC LIMIT 10")->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-speedometer2 me-2 text-green"></i>Admin Dashboard</h2>
        <p>Overview of waste management operations across Nairobi.</p>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-blue"><i class="bi bi-file-earmark-text"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Reports</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-orange"><i class="bi bi-clock"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-purple"><i class="bi bi-arrow-repeat"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['completed']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-red"><i class="bi bi-people"></i></div>
                </div>
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Users</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-green"><i class="bi bi-star"></i></div>
                </div>
                <div class="stat-value"><?php echo number_format($avgRating, 1); ?></div>
                <div class="stat-label">Avg Rating</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Status Distribution Pie Chart -->
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="bi bi-pie-chart me-2"></i>Status Distribution</h5>
                </div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Reports by Location Bar Chart -->
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="bi bi-bar-chart me-2"></i>Reports by Location</h5>
                </div>
                <div class="chart-container">
                    <canvas id="locationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Line Chart -->
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="bi bi-graph-up me-2"></i>Report Trends</h5>
                </div>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Map Section -->
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="bi bi-map me-2"></i>Nairobi Area Map</h5>
                    <span class="badge bg-success">Click to filter</span>
                </div>
                <p class="text-muted small mb-3">Click on an area to filter reports by location.</p>
                <div class="map-container">
                    <img src="/smart waste system/assets/images/nairobi_map.svg" alt="Nairobi Map" usemap="#nairobiMap" 
                         id="nairobiMapImg" style="width:100%; max-width:500px;">
                    <map name="nairobiMap" id="nairobiMap">
                        <!-- Clickable zones mapped to Nairobi area positions in the SVG -->
                        <area shape="rect" coords="190,115,285,170" alt="CBD" data-location="CBD" 
                              href="#" title="CBD - Central Business District">
                        <area shape="rect" coords="160,45,280,100" alt="Westlands" data-location="Westlands" 
                              href="#" title="Westlands">
                        <area shape="rect" coords="300,110,420,165" alt="Eastleigh" data-location="Eastleigh" 
                              href="#" title="Eastleigh">
                        <area shape="rect" coords="120,210,235,270" alt="Kibera" data-location="Kibera" 
                              href="#" title="Kibera">
                        <area shape="rect" coords="100,290,230,355" alt="Langata" data-location="Langata" 
                              href="#" title="Langata">
                        <area shape="rect" coords="15,290,90,355" alt="Karen" data-location="Karen" 
                              href="#" title="Karen">
                        <area shape="rect" coords="300,35,420,95" alt="Kasarani" data-location="Kasarani" 
                              href="#" title="Kasarani">
                        <area shape="rect" coords="335,180,465,255" alt="Embakasi" data-location="Embakasi" 
                              href="#" title="Embakasi">
                        <area shape="rect" coords="30,120,170,185" alt="Dagoretti" data-location="Dagoretti" 
                              href="#" title="Dagoretti">
                        <area shape="rect" coords="210,185,310,235" alt="Starehe" data-location="Starehe" 
                              href="#" title="Starehe">
                    </map>
                </div>
            </div>
        </div>

        <!-- Filtered Reports Display -->
        <div class="col-lg-7">
            <div id="mapFilterInfo"></div>
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h5><i class="bi bi-table me-2"></i>Recent Reports</h5>
                    <a href="/smart waste system/admin/reports.php" class="btn btn-sm btn-outline-secondary">Manage All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0" id="reportsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reported By</th>
                                <th>Description</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $report): ?>
                                <tr>
                                    <td><strong><?php echo $report['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($report['description'], 0, 40)) . '...'; ?></td>
                                    <td class="report-location"><?php echo htmlspecialchars($report['location']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-' . $report['status'];
                                        echo '<span class="badge ' . $badgeClass . '">' . ucfirst(str_replace('-', ' ', $report['status'])) . '</span>';
                                        ?>
                                    </td>
                                    <td><?php echo date('M d', strtotime($report['date_submitted'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Status Distribution Pie Chart ────────────────────────
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Assigned', 'In Progress', 'Completed', 'Archived'],
            datasets: [{
                data: [
                    <?php echo $stats['pending']; ?>,
                    <?php echo $stats['assigned']; ?>,
                    <?php echo $stats['in_progress']; ?>,
                    <?php echo $stats['completed']; ?>,
                    <?php echo $stats['archived']; ?>
                ],
                backgroundColor: [
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(41, 128, 185, 0.8)',
                    'rgba(142, 68, 173, 0.8)',
                    'rgba(39, 174, 96, 0.8)',
                    'rgba(99, 110, 114, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true }
                }
            }
        }
    });

    // ── Reports by Location Bar Chart ────────────────────────
    const locationCtx = document.getElementById('locationChart').getContext('2d');
    new Chart(locationCtx, {
        type: 'bar',
        data: {
            labels: <?php echo $locationLabels; ?>,
            datasets: [{
                label: 'Reports',
                data: <?php echo $locationCounts; ?>,
                backgroundColor: 'rgba(27, 94, 32, 0.7)',
                borderColor: 'rgba(27, 94, 32, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // ── Monthly Trend Line Chart ─────────────────────────────
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo $trendLabels; ?>,
            datasets: [{
                label: 'Reports',
                data: <?php echo $trendCounts; ?>,
                borderColor: 'rgba(249, 168, 37, 1)',
                backgroundColor: 'rgba(249, 168, 37, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(249, 168, 37, 1)',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
