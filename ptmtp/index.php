<?php
session_start();
include 'conn.php';
include 'auth.php';

$is_logged_in = isset($_SESSION['user_id']);
$plantation_count = (int) scalar($con, "SELECT COUNT(*) FROM plantations");
$owner_count = (int) scalar($con, "SELECT COUNT(*) FROM users WHERE role='owner'");
$tree_count = (int) scalar($con, "SELECT COALESCE(SUM(quantity),0) FROM trees");
$public_page = true;
$page_title = 'Pine Tree Management';
include 'includes/header.php';
?>
<header class="landing-header">
    <div class="container landing-nav">
        <a class="landing-brand" href="index.php">
            <span><i class="bi bi-tree-fill"></i></span>
            <div>PTMPT<small>Pine Tree Management</small></div>
        </a>
        <button class="landing-menu" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav"><i
                class="bi bi-list"></i></button>
        <nav class="collapse landing-links" id="landingNav">
            <a href="#features">Features</a>
            <a href="#roles">Who it serves</a>
            <a href="#workflow">How it works</a>
            <a href="#about">About</a>
            <?php if ($is_logged_in): ?>
                <a class="btn btn-success px-4" href="dashboard.php">Open dashboard</a>
            <?php else: ?>
                <a href="login.php">Sign in</a>
                <a class="btn btn-success px-4" href="register.php">Register as owner</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="landing-main">
    <section class="landing-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="landing-kicker"><i class="bi bi-leaf-fill"></i> Digital forestry management for
                        Rwanda</span>
                    <h1>Manage pine plantations with clarity and confidence.</h1>
                    <p class="landing-lead">One secure platform for plantation records, tree growth, timber valuation,
                        GIS locations, and transparent owner payments.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a class="btn btn-success btn-lg px-4"
                            href="<?= $is_logged_in ? 'dashboard.php' : 'login.php' ?>">
                            <?= $is_logged_in ? 'Go to dashboard' : 'Access the system' ?> <i
                                class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a class="btn btn-outline-dark btn-lg px-4" href="#features">Explore features</a>
                    </div>
                    <div class="landing-trust">
                        <div><i class="bi bi-shield-check"></i><span><strong>Secure access</strong>Role-based
                                permissions</span></div>
                        <div><i class="bi bi-eye"></i><span><strong>Transparent</strong>Auditable records</span></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="landing-preview">
                        <div class="preview-top">
                            <div><span></span><span></span><span></span></div>
                            <small>PTMPT Operations Overview</small>
                        </div>
                        <div class="preview-body">
                            <div class="preview-sidebar">
                                <i class="bi bi-grid-fill active"></i><i class="bi bi-tree"></i><i
                                    class="bi bi-flower1"></i><i class="bi bi-geo-alt"></i><i class="bi bi-wallet2"></i>
                            </div>
                            <div class="preview-content">
                                <div class="preview-welcome"><small>FORESTRY OVERVIEW</small><strong>Healthy forests.
                                        Better records.</strong></div>
                                <div class="preview-stats">
                                    <div><i
                                            class="bi bi-tree-fill"></i><small>Plantations</small><strong><?= number_format($plantation_count) ?></strong>
                                    </div>
                                    <div><i
                                            class="bi bi-people-fill"></i><small>Owners</small><strong><?= number_format($owner_count) ?></strong>
                                    </div>
                                    <div><i class="bi bi-flower1"></i><small>Trees
                                            recorded</small><strong><?= number_format($tree_count) ?></strong></div>
                                </div>
                                <div class="preview-chart">
                                    <div class="chart-heading"><span>Plantation growth</span><small>Live database
                                            insights</small></div>
                                    <div class="bar-set"><i style="height:32%"></i><i style="height:48%"></i><i
                                            style="height:42%"></i><i style="height:70%"></i><i
                                            style="height:58%"></i><i style="height:88%"></i><i style="height:76%"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-strip">
        <div class="container">
            <div><strong><?= number_format($plantation_count) ?></strong><span>Registered plantations</span></div>
            <div><strong><?= number_format($owner_count) ?></strong><span>Plantation owners</span></div>
            <div><strong><?= number_format($tree_count) ?></strong><span>Trees in digital records</span></div>
            <div><strong>5</strong><span>Specialized user roles</span></div>
        </div>
    </section>

    <section class="landing-section" id="features">
        <div class="container">
            <div class="section-heading">
                <span class="landing-kicker">Platform capabilities</span>
                <h2>Everything forestry operations need in one place.</h2>
                <p>Replace scattered paperwork with reliable, searchable, and role-controlled digital records.</p>
            </div>
            <div class="row g-4">
                <?php
                $features = [
                    ['tree-fill', 'Plantation records', 'Register land, ownership, district, sector, area, and precise GPS coordinates.'],
                    ['flower1', 'Tree monitoring', 'Track species, quantity, age, and growth from seedling through harvest readiness.'],
                    ['graph-up-arrow', 'Timber valuation', 'Automatically estimate timber value from recorded tree quantity and age.'],
                    ['geo-alt-fill', 'GIS mapping', 'See registered plantation locations through an interactive OpenStreetMap view.'],
                    ['clipboard-check', 'License approvals', 'Owners and buyers request planting, cutting, or buying approval before forestry work continues.'],
                    ['wallet2', 'Payment tracking', 'NAEB officers create, approve, reject, and review owner payments with a clear status history.'],
                    ['file-earmark-bar-graph-fill', 'Operational reports', 'Generate printable plantation, owner, payment, timber value, and request reports.']
                ];
                foreach ($features as [$icon, $title, $copy]):
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="landing-feature">
                            <span><i class="bi bi-<?= e($icon) ?>"></i></span>
                            <h3><?= e($title) ?></h3>
                            <p><?= e($copy) ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section-dark" id="roles">
        <div class="container">
            <div class="section-heading section-heading-light">
                <span class="landing-kicker">Built for every stakeholder</span>
                <h2>Each user sees the tools and data they need.</h2>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl">
                    <article class="role-card"><span>01</span><i class="bi bi-person-gear"></i>
                        <h3>Administrator</h3>
                        <p>Manages system users and prints official reports.</p>
                    </article>
                </div>
                <div class="col-md-6 col-xl">
                    <article class="role-card"><span>02</span><i class="bi bi-tree"></i>
                        <h3>Forestry Officer</h3>
                        <p>Approves planting, cutting, and buying requests before field activity continues.</p>
                    </article>
                </div>
                <div class="col-md-6 col-xl">
                    <article class="role-card"><span>03</span><i class="bi bi-bank"></i>
                        <h3>NAEB Officer</h3>
                        <p>Registers officers and manages owner payments.</p>
                    </article>
                </div>
                <div class="col-md-6 col-xl">
                    <article class="role-card"><span>04</span><i class="bi bi-person-check"></i>
                        <h3>Plantation Owner</h3>
                        <p>Views personal records and requests licenses to plant or cut trees.</p>
                    </article>
                </div>
                <div class="col-md-6 col-xl">
                    <article class="role-card"><span>05</span><i class="bi bi-cart-check"></i>
                        <h3>Buyer</h3>
                        <p>Requests approval to buy trees and tracks forestry decisions.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section" id="workflow">
        <div class="container">
            <div class="row align-items-start g-5">
                <div class="col-lg-5">
                    <span class="landing-kicker">Simple operational flow</span>
                    <h2 class="landing-side-title">From field registration to owner payment.</h2>
                    <p class="text-muted">PTMPT connects forestry data and financial operations without losing
                        accountability between teams.</p>
                    <a class="btn btn-success mt-2" href="<?= $is_logged_in ? 'dashboard.php' : 'login.php' ?>">Start
                        using PTMPT</a>
                </div>
                <div class="col-lg-7">
                    <div class="workflow-list">
                        <div><span>1</span>
                            <article>
                                <h3>Submit a forestry request</h3>
                                <p>Owners request planting/cutting licenses and buyers request approval to buy trees.</p>
                            </article>
                        </div>
                        <div><span>2</span>
                            <article>
                                <h3>Forestry officer approval</h3>
                                <p>Planting, cutting, and buying requests must be approved before action continues.</p>
                            </article>
                        </div>
                        <div><span>3</span>
                            <article>
                                <h3>Record plantation and tree data</h3>
                                <p>Approved field work becomes traceable through plantation, GIS, and tree records.</p>
                            </article>
                        </div>
                        <div><span>4</span>
                            <article>
                                <h3>Process and track payments</h3>
                                <p>NAEB officers record decisions while owners receive transparent status visibility.
                                </p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-cta" id="about">
        <div class="container">
            <div>
                <span class="landing-kicker">Better forestry administration</span>
                <h2>Ready to manage plantations digitally?</h2>
                <p>Access reliable records, actionable dashboards, maps, reports, and transparent payments.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-light btn-lg"
                    href="<?= $is_logged_in ? 'dashboard.php' : 'login.php' ?>"><?= $is_logged_in ? 'Open dashboard' : 'Sign in' ?></a>
                <?php if (!$is_logged_in): ?><a class="btn btn-outline-light btn-lg" href="register.php">Owner
                        registration</a><?php endif; ?>
            </div>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <div class="container">
        <a class="landing-brand" href="index.php"><span><i class="bi bi-tree-fill"></i></span>
            <div>PTMPT<small>Pine Tree Management</small></div>
        </a>
        <p>Pine Tree Management and Payment Tracking System for Rwanda's forestry sector.</p>
        <span>&copy; <?= date('Y') ?> NAEB. All rights reserved.</span>
    </div>
</footer>
<?php include 'includes/footer.php'; ?>
