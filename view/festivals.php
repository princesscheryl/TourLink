<?php
require_once '../settings/core.php';
require_once '../classes/festival_class.php';

$festival_class = new Festival();
$festivals = $festival_class->get_all_festivals();
$featured = $festival_class->get_featured_festivals(3);

// Group by month for calendar view
$months = [];
foreach ($festivals as $festival) {
    $month = date('F Y', strtotime($festival['start_date']));
    if (!isset($months[$month])) {
        $months[$month] = [];
    }
    $months[$month][] = $festival;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghana Festival Calendar - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <link href="../css/festivals.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/accessibility.js"></script>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="page-header">
        <div class="container-main">
            <h1>Ghana Festival Calendar</h1>
            <p>Experience Ghana's rich cultural heritage through vibrant festivals celebrated across all regions</p>
        </div>
    </div>

    <?php if ($featured): ?>
    <section class="featured-section">
        <div class="container-main">
            <h2 class="section-title">
                <i class="fas fa-star"></i>
                Featured Festivals
            </h2>
            <div class="featured-grid">
                <?php foreach ($featured as $fest): ?>
                <div class="festival-card">
                    <div class="festival-image">
                        <?php if (!empty($fest['image_url'])): ?>
                            <?php
                            // Add ../ prefix if the path doesn't start with http or /
                            $image_src = $fest['image_url'];
                            if (!preg_match('/^(https?:\/\/|\/)/i', $image_src)) {
                                $image_src = '../' . $image_src;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($image_src); ?>"
                                 alt="<?php echo htmlspecialchars($fest['festival_name']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <i class="fas fa-drum" style="display: none;"></i>
                        <?php else: ?>
                            <i class="fas fa-drum"></i>
                        <?php endif; ?>
                        <div class="festival-date-badge">
                            <?php echo date('M d', strtotime($fest['start_date'])); ?>
                        </div>
                        <div class="festival-type-badge">
                            <?php echo htmlspecialchars($fest['festival_type']); ?>
                        </div>
                    </div>
                    <div class="festival-content">
                        <h3 class="festival-name"><?php echo htmlspecialchars($fest['festival_name']); ?></h3>
                        <p class="festival-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($fest['location'] . ', ' . $fest['region']); ?>
                        </p>
                        <p class="festival-description">
                            <?php echo htmlspecialchars(substr($fest['description'], 0, 120)); ?>...
                        </p>
                        <a href="all_services.php?region=<?php echo urlencode($fest['region']); ?>" class="festival-cta">
                            Find services nearby <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="calendar-section">
        <div class="container-main">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Full Festival Calendar
            </h2>

            <?php foreach ($months as $month => $month_festivals): ?>
            <div class="month-group">
                <h3 class="month-title"><?php echo $month; ?></h3>
                <div class="festival-list">
                    <?php foreach ($month_festivals as $fest): ?>
                    <div class="festival-list-item">
                        <div class="date-box">
                            <div class="date-day"><?php echo date('d', strtotime($fest['start_date'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($fest['start_date'])); ?></div>
                        </div>
                        <div class="festival-info">
                            <h3><?php echo htmlspecialchars($fest['festival_name']); ?></h3>
                            <p class="festival-meta">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($fest['location']); ?>
                            </p>
                        </div>
                        <span class="festival-region">
                            <i class="fas fa-globe-africa"></i>
                            <?php echo htmlspecialchars($fest['region']); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($months)): ?>
            <div style="text-align: center; padding: 60px; color: #6b7280;">
                <i class="fas fa-calendar-times fa-3x" style="opacity: 0.3; margin-bottom: 16px;"></i>
                <p>No festivals scheduled yet. Check back soon!</p>
            </div>
            <?php endif; ?>

            <div class="cta-section">
                <h2>Plan Your Festival Experience</h2>
                <p>Book local tour guides, transportation, and accommodation for your festival visit</p>
                <a href="all_services.php" class="btn-cta">
                    <i class="fas fa-search"></i>
                    Browse Services
                </a>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
