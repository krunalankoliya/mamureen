<?php
$current_page = 'tnc_members_list';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$safe_mauze = mysqli_real_escape_string($mysqli, $mauze);

$query   = "SELECT `fullname`, `post`, `mobile`, `email` FROM `tnc_members_list` WHERE `jamaat` = '$safe_mauze' ORDER BY `fullname` ASC";
$result  = mysqli_query($mysqli, $query);
$members = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Avatar colour palette — cycles by index
$palette = ['#4154f1','#2eca6a','#ff771d','#0dcaf0','#6f42c1','#dc3545','#fd7e14','#198754'];

function getInitials($name) {
    $parts = explode(' ', trim($name));
    $init  = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $init .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $init ?: '?';
}
?>

<style>
.tnc-hero {
    background: linear-gradient(135deg, #4154f1 0%, #6a85f5 100%);
    border-radius: 0.75rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
}
.tnc-hero-icon {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 1.5rem;
}
.tnc-hero h4 { margin: 0; font-weight: 700; font-size: 1.1rem; }
.tnc-hero p  { margin: 0; font-size: 0.85rem; opacity: 0.85; }
.tnc-count-badge {
    margin-left: auto;
    background: rgba(255,255,255,0.25);
    border-radius: 2rem;
    padding: 0.35rem 1rem;
    font-size: 1rem;
    font-weight: 700;
    white-space: nowrap;
}

.member-card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: transform 0.18s, box-shadow 0.18s;
    height: 100%;
}
.member-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.13);
}
.member-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    color: #fff;
    flex-shrink: 0;
    letter-spacing: 0.5px;
}
.member-name {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 1.3;
    color: #212529;
    margin-bottom: 0;
}
.post-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.2em 0.65em;
    border-radius: 2rem;
    background: #eef0fd;
    color: #4154f1;
    margin-top: 0.2rem;
    letter-spacing: 0.2px;
}
.member-meta {
    font-size: 0.8rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.55rem;
}
.member-meta i { font-size: 0.85rem; flex-shrink: 0; }
.wa-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #e6f9ee;
    color: #1a8a42;
    border-radius: 2rem;
    padding: 0.3rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s;
    margin-top: 0.6rem;
}
.wa-pill:hover { background: #c8f0d8; color: #145e2d; }
.wa-pill i { font-size: 0.95rem; }

.search-wrap {
    position: relative;
    max-width: 360px;
}
.search-wrap .search-icon {
    position: absolute;
    left: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 0.95rem;
}
.search-wrap input {
    padding-left: 2.2rem;
    border-radius: 2rem;
    border: 1px solid #dee2e6;
    font-size: 0.88rem;
}
.search-wrap input:focus {
    border-color: #4154f1;
    box-shadow: 0 0 0 3px rgba(65,84,241,0.12);
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>TNC Members List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo MODULE_PATH ?>index.php">Home</a></li>
                <li class="breadcrumb-item active">TNC Members</li>
            </ol>
        </nav>
    </div>

    <section class="section">

        <!-- Hero banner -->
        <div class="tnc-hero">
            <div class="tnc-hero-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <h4>TNC Members</h4>
                <p><?php echo htmlspecialchars($mauze) ?></p>
            </div>
            <div class="tnc-count-badge">
                <i class="bi bi-person-check me-1"></i><?php echo count($members) ?> Members
            </div>
        </div>

        <?php if (empty($members)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No TNC members found for your jamaat.
            </div>
        <?php else: ?>

        <!-- Search bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="search-wrap flex-grow-1 flex-sm-grow-0">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="memberSearch" class="form-control" placeholder="Search by name or post…">
            </div>
        </div>

        <!-- Member cards grid -->
        <div class="row g-3" id="memberGrid">
            <?php foreach ($members as $i => $m): ?>
            <?php
                $color   = $palette[$i % count($palette)];
                $initials = getInitials($m['fullname']);
                $searchData = strtolower($m['fullname'] . ' ' . $m['post'] . ' ' . $m['mobile'] . ' ' . $m['email']);
                $waNumber = preg_replace('/[^0-9]/', '', $m['mobile']);
            ?>
            <div class="col-12 col-sm-6 col-lg-4 member-card-wrap" data-search="<?php echo htmlspecialchars($searchData) ?>">
                <div class="card member-card">
                    <div class="card-body d-flex gap-3 align-items-start">
                        <div class="member-avatar" style="background:<?php echo $color ?>">
                            <?php echo $initials ?>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <p class="member-name"><?php echo htmlspecialchars($m['fullname']) ?></p>
                            <?php if ($m['post']): ?>
                                <span class="post-badge"><?php echo htmlspecialchars($m['post']) ?></span>
                            <?php endif; ?>

                            <?php if ($m['mobile']): ?>
                                <div>
                                    <a href="https://wa.me/<?php echo $waNumber ?>" target="_blank" class="wa-pill">
                                        <i class="bi bi-whatsapp"></i><?php echo htmlspecialchars($m['mobile']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($m['email']): ?>
                                <div class="member-meta">
                                    <i class="bi bi-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($m['email']) ?>" class="text-truncate text-decoration-none text-secondary">
                                        <?php echo htmlspecialchars($m['email']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="noResults" class="alert alert-warning mt-3" style="display:none;">
            <i class="bi bi-search me-2"></i>No members match your search.
        </div>

        <?php endif; ?>

    </section>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
<script src="<?php echo MODULE_PATH ?>assets/js/tnc_members_list.js"></script>
<?php require_once __DIR__ . '/inc/js-block.php'; ?>
