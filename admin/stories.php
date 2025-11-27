<?php
require_once 'includes_platform/auth_check.php';
require_privilege('view_dashboard');

// Sample success stories data
// In production, this would fetch from tl_success_stories table
$stories = [
    [
        'story_id' => 1,
        'title' => 'From Local Guide to Regional Business Owner',
        'provider_name' => 'Kwame Mensah',
        'business_name' => 'Accra Heritage Tours',
        'region' => 'Greater Accra',
        'category' => 'Tour Guide',
        'excerpt' => 'Within 6 months of joining TourLink, I expanded from solo tours to hiring 3 additional guides...',
        'views' => 1250,
        'is_featured' => true,
        'is_published' => true,
        'date_created' => '2024-11-15',
        'provider_image' => null
    ],
    [
        'story_id' => 2,
        'title' => 'Crafting Success: How TourLink Transformed My Artisan Business',
        'provider_name' => 'Abena Osei',
        'business_name' => 'Kumasi Traditional Crafts',
        'region' => 'Ashanti',
        'category' => 'Artisan',
        'excerpt' => 'TourLink connected me with international tourists who appreciate authentic Ghanaian crafts...',
        'views' => 890,
        'is_featured' => true,
        'is_published' => true,
        'date_created' => '2024-10-22',
        'provider_image' => null
    ],
    [
        'story_id' => 3,
        'title' => 'Driving Forward: Building a Transport Business Through TourLink',
        'provider_name' => 'Yaw Boateng',
        'business_name' => 'SafeRide Ghana',
        'region' => 'Greater Accra',
        'category' => 'Driver',
        'excerpt' => 'Started with one car, now managing a fleet of five vehicles serving tourists across Ghana...',
        'views' => 670,
        'is_featured' => false,
        'is_published' => true,
        'date_created' => '2024-09-18',
        'provider_image' => null
    ],
    [
        'story_id' => 4,
        'title' => 'Cultural Ambassador: Sharing Our Heritage With the World',
        'provider_name' => 'Ama Adjei',
        'business_name' => 'Ga Cultural Experiences',
        'region' => 'Greater Accra',
        'category' => 'Cultural Guide',
        'excerpt' => 'TourLink gave me a platform to share Ga traditions with visitors from around the globe...',
        'views' => 540,
        'is_featured' => false,
        'is_published' => true,
        'date_created' => '2024-08-30',
        'provider_image' => null
    ],
    [
        'story_id' => 5,
        'title' => 'Coastal Adventures: Growing My Beach Tour Business',
        'provider_name' => 'Kojo Mensah',
        'business_name' => 'Cape Coast Adventures',
        'region' => 'Central',
        'category' => 'Tour Guide',
        'excerpt' => 'The platform helped me reach tourists interested in Ghana\'s beautiful coastline...',
        'views' => 420,
        'is_featured' => false,
        'is_published' => false,
        'date_created' => '2024-11-20',
        'provider_image' => null
    ],
    [
        'story_id' => 6,
        'title' => 'Empowering Women Through Tourism',
        'provider_name' => 'Efua Dadzie',
        'business_name' => 'Women of Ghana Tours',
        'region' => 'Ashanti',
        'category' => 'Tour Guide',
        'excerpt' => 'TourLink enabled me to create employment opportunities for women in my community...',
        'views' => 780,
        'is_featured' => true,
        'is_published' => true,
        'date_created' => '2024-10-05',
        'provider_image' => null
    ]
];

// Calculate statistics
$total_stories = count($stories);
$published_stories = 0;
$featured_stories = 0;
$total_views = 0;

foreach ($stories as $story) {
    if ($story['is_published']) {
        $published_stories++;
    }
    if ($story['is_featured']) {
        $featured_stories++;
    }
    $total_views += $story['views'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success Stories - TourLink Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }

        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: #1b4332; padding: 24px 0; overflow-y: auto; z-index: 100; }
        .sidebar-brand { padding: 0 24px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px; }
        .sidebar-brand a { color: white; text-decoration: none; font-size: 22px; font-weight: 700; }
        .sidebar-brand span { color: #d4a017; }
        .sidebar-brand small { display: block; color: rgba(255,255,255,0.6); font-size: 11px; margin-top: 4px; }
        .nav-section { padding: 0 16px; margin-bottom: 24px; }
        .nav-section-title { color: rgba(255,255,255,0.4); font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0 8px; margin-bottom: 8px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500; transition: all 0.2s; margin-bottom: 2px; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        .nav-link i { width: 18px; font-size: 14px; }

        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-bar { background: white; padding: 16px 32px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .page-title { font-size: 18px; font-weight: 600; color: #1e293b; }
        .user-menu { display: flex; align-items: center; gap: 12px; }
        .user-info { text-align: right; }
        .user-name { font-size: 13px; font-weight: 600; color: #1e293b; }
        .user-role { font-size: 11px; color: #64748b; }
        .user-avatar { width: 36px; height: 36px; background: #1b4332; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }

        .content-area { padding: 32px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 18px; }
        .stat-icon.blue { background: #dbeafe; color: #1e40af; }
        .stat-icon.green { background: #dcfce7; color: #166534; }
        .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
        .stat-icon.orange { background: #fed7aa; color: #c2410c; }
        .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .stat-label { font-size: 13px; color: #64748b; }

        .card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 16px; font-weight: 600; color: #1e293b; }
        .card-body { padding: 0; }

        .toolbar { display: flex; gap: 12px; padding: 20px 24px; flex-wrap: wrap; border-bottom: 1px solid #f1f5f9; }
        .search-box { flex: 1; min-width: 280px; position: relative; }
        .search-box input { width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; transition: all 0.2s; }
        .search-box input:focus { outline: none; border-color: #1b4332; }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        .filter-select { padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; background: white; cursor: pointer; }

        .stories-list { padding: 24px; }
        .story-item { padding: 20px 0; border-bottom: 1px solid #f1f5f9; display: flex; gap: 20px; transition: all 0.2s; }
        .story-item:hover { background: #f8fafc; margin: 0 -24px; padding: 20px 24px; }
        .story-item:last-child { border-bottom: none; }
        .story-image { width: 120px; height: 120px; border-radius: 12px; background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; flex-shrink: 0; }
        .story-content { flex: 1; min-width: 0; }
        .story-header { display: flex; align-items: start; justify-content: space-between; gap: 16px; margin-bottom: 8px; }
        .story-title { font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 6px; }
        .story-meta { display: flex; gap: 16px; font-size: 12px; color: #64748b; margin-bottom: 10px; }
        .story-meta span { display: flex; align-items: center; gap: 4px; }
        .story-excerpt { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 12px; }
        .story-footer { display: flex; gap: 12px; align-items: center; }
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-published { background: #dcfce7; color: #166534; }
        .badge-draft { background: #fee2e2; color: #991b1b; }
        .badge-featured { background: #fef3c7; color: #92400e; }
        .story-actions { margin-left: auto; display: flex; gap: 8px; }
        .btn-action { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 13px; }
        .btn-view { background: #dbeafe; color: #1e40af; }
        .btn-view:hover { background: #bfdbfe; }
        .btn-feature { background: #fef3c7; color: #92400e; }
        .btn-feature:hover { background: #fde68a; }
        .btn-edit { background: #f3e8ff; color: #7c3aed; }
        .btn-edit:hover { background: #e9d5ff; }

        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .story-item { flex-direction: column; }
            .story-image { width: 100%; height: 160px; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="platform_dashboard.php">TourLink<span>.</span></a>
            <small>Administration</small>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Overview</div>
            <a href="platform_dashboard.php" class="nav-link">
                <i class="fas fa-th-large"></i>
                Dashboard
            </a>
            <a href="impact.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                Impact Metrics
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="manage_providers.php" class="nav-link">
                <i class="fas fa-store"></i>
                Providers
            </a>
            <a href="manage_bookings.php" class="nav-link">
                <i class="fas fa-calendar-check"></i>
                Bookings
            </a>
            <a href="services.php" class="nav-link">
                <i class="fas fa-concierge-bell"></i>
                Services
            </a>
            <a href="manage_discounts.php" class="nav-link">
                <i class="fas fa-tags"></i>
                Discount Codes
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="manage_festivals.php" class="nav-link">
                <i class="fas fa-drum"></i>
                Festivals
            </a>
            <a href="regions.php" class="nav-link">
                <i class="fas fa-map-marked-alt"></i>
                Regions
            </a>
            <a href="stories.php" class="nav-link active">
                <i class="fas fa-book-open"></i>
                Success Stories
            </a>
        </div>

        <div class="nav-section" style="margin-top: auto;">
            <a href="platform_logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Sign Out
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Success Stories</h1>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name">System Admin</div>
                    <div class="user-role">Super Admin</div>
                </div>
                <div class="user-avatar">S</div>
            </div>
        </div>

        <div class="content-area">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
                    <div class="stat-value"><?php echo $total_stories; ?></div>
                    <div class="stat-label">Total Stories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo $published_stories; ?></div>
                    <div class="stat-label">Published Stories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-star"></i></div>
                    <div class="stat-value"><?php echo $featured_stories; ?></div>
                    <div class="stat-label">Featured Stories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-eye"></i></div>
                    <div class="stat-value"><?php echo number_format($total_views); ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Success Stories</h2>
                </div>
                <div class="card-body">
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search stories by title, provider, or region...">
                        </div>
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                        <select class="filter-select" id="featuredFilter">
                            <option value="all">All Stories</option>
                            <option value="featured">Featured Only</option>
                        </select>
                    </div>

                    <div class="stories-list">
                        <?php foreach ($stories as $story): ?>
                        <div class="story-item" data-status="<?php echo $story['is_published'] ? 'published' : 'draft'; ?>" data-featured="<?php echo $story['is_featured'] ? 'featured' : 'regular'; ?>">
                            <div class="story-image">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="story-content">
                                <div class="story-header">
                                    <div>
                                        <div class="story-title"><?php echo htmlspecialchars($story['title']); ?></div>
                                        <div class="story-meta">
                                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($story['provider_name']); ?></span>
                                            <span><i class="fas fa-store"></i> <?php echo htmlspecialchars($story['business_name']); ?></span>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($story['region']); ?></span>
                                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($story['date_created'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="story-excerpt"><?php echo htmlspecialchars($story['excerpt']); ?></div>
                                <div class="story-footer">
                                    <?php if ($story['is_published']): ?>
                                        <span class="badge badge-published">Published</span>
                                    <?php else: ?>
                                        <span class="badge badge-draft">Draft</span>
                                    <?php endif; ?>
                                    <?php if ($story['is_featured']): ?>
                                        <span class="badge badge-featured">Featured</span>
                                    <?php endif; ?>
                                    <span style="font-size: 12px; color: #64748b; margin-left: 12px;">
                                        <i class="fas fa-eye"></i> <?php echo number_format($story['views']); ?> views
                                    </span>
                                    <div class="story-actions">
                                        <button class="btn-action btn-view" onclick="viewStory(<?php echo $story['story_id']; ?>)" title="View Story">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-feature" onclick="toggleFeature(<?php echo $story['story_id']; ?>, <?php echo $story['is_featured'] ? 'false' : 'true'; ?>)" title="<?php echo $story['is_featured'] ? 'Remove Featured' : 'Feature Story'; ?>">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editStory(<?php echo $story['story_id']; ?>)" title="Edit Story">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Filter and search functionality
        document.getElementById('searchInput').addEventListener('input', filterStories);
        document.getElementById('statusFilter').addEventListener('change', filterStories);
        document.getElementById('featuredFilter').addEventListener('change', filterStories);

        function filterStories() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const featuredFilter = document.getElementById('featuredFilter').value;
            const stories = document.querySelectorAll('.story-item');

            stories.forEach(story => {
                const text = story.textContent.toLowerCase();
                const status = story.getAttribute('data-status');
                const featured = story.getAttribute('data-featured');

                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;
                const matchesFeatured = featuredFilter === 'all' || featured === featuredFilter;

                story.style.display = matchesSearch && matchesStatus && matchesFeatured ? 'flex' : 'none';
            });
        }

        function viewStory(storyId) {
            // Navigate to story detail view
            alert('View story functionality - Story ID: ' + storyId + '\n\nThis would navigate to a detailed view of the success story.');
        }

        function toggleFeature(storyId, featured) {
            const action = featured ? 'feature' : 'unfeature';
            if (confirm('Are you sure you want to ' + action + ' this story?')) {
                alert('Toggle feature functionality - Story ID: ' + storyId + '\n\nThis would update the featured status in the database.');
                // Reload page to reflect changes
                // window.location.reload();
            }
        }

        function editStory(storyId) {
            // Navigate to story edit page
            alert('Edit story functionality - Story ID: ' + storyId + '\n\nThis would navigate to a form to edit the success story details.');
        }
    </script>
</body>
</html>
