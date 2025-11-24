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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        .page-header {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: white;
            padding: 120px 0 60px;
        }

        .page-header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
        }

        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Featured Festivals */
        .featured-section {
            padding: 60px 0;
            background: white;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #d4a017;
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .festival-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e5e7eb;
        }

        .festival-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .festival-image {
            height: 180px;
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .festival-image i {
            font-size: 48px;
            color: rgba(255,255,255,0.3);
        }

        .festival-date-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            background: #d4a017;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .festival-type-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255,255,255,0.9);
            color: #1b4332;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .festival-content {
            padding: 24px;
        }

        .festival-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 8px;
        }

        .festival-location {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .festival-description {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .festival-cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #1b4332;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .festival-cta:hover {
            color: #d4a017;
        }

        /* Calendar Section */
        .calendar-section {
            padding: 60px 0;
        }

        .month-group {
            margin-bottom: 48px;
        }

        .month-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .festival-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .festival-list-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 24px;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .festival-list-item:hover {
            border-color: #1b4332;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .date-box {
            text-align: center;
            background: #f0fdf4;
            padding: 12px;
            border-radius: 8px;
        }

        .date-day {
            font-size: 24px;
            font-weight: 700;
            color: #1b4332;
            line-height: 1;
        }

        .date-month {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .festival-info h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .festival-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .festival-region {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f3f4f6;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #4b5563;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            padding: 60px;
            border-radius: 20px;
            text-align: center;
            color: white;
            margin: 40px 0 60px;
        }

        .cta-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .cta-section p {
            opacity: 0.9;
            margin-bottom: 24px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #d4a017;
            color: #1b4332;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-cta:hover {
            background: #b8860b;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .featured-grid {
                grid-template-columns: 1fr;
            }
            .festival-list-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .date-box {
                justify-self: center;
            }
        }
    </style>
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
                        <i class="fas fa-drum"></i>
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
