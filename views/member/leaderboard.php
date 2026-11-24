<?php
require_once '../../controllers/member/leaderboard_page_controller.php';
require_once '../../includes/headers/header_member.php';

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}

$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$podiumOrder = [1, 0, 2];
$initials = static function (array $member): string {
    return strtoupper(
        mb_substr((string)($member['First_Name'] ?? ''), 0, 1)
        . mb_substr((string)($member['Last_Name'] ?? ''), 0, 1)
    );
};
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        <section class="mb-12">
            <div class="content-header-row mb-4">
                <div>
                    <h2 class="page-main-heading m-0">Gym Visit Leaderboard</h2>
                    <p class="page-sub-heading m-0">Earn 10 points for each completed gym visit of 30 minutes or more.</p>
                </div>
            </div>

            <div class="ld-podium-wrap" aria-label="Top three members">
                <?php foreach ($podiumOrder as $index): ?>
                    <?php if (!isset($podium_rankings[$index])) { continue; } ?>
                    <?php
                    $member = $podium_rankings[$index];
                    $podiumClass = $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : 'rank-3');
                    $image = basename((string)($member['Profile_Image'] ?? ''));
                    ?>
                    <div class="ld-podium-item <?= $podiumClass ?>">
                        <div class="ld-avatar-container">
                            <?php if ($index === 0): ?>
                                <span class="material-symbols-outlined ld-crown" aria-hidden="true">workspace_premium</span>
                            <?php endif; ?>
                            <?php if ($image !== '' && $image !== 'default_avatar.png'): ?>
                                <img alt="" class="ld-avatar" src="../../assets/images/uploads/<?= $escape($image) ?>">
                            <?php else: ?>
                                <div class="ld-avatar ld-avatar-initials"><?= $escape($initials($member)) ?></div>
                            <?php endif; ?>
                            <div class="ld-badge"><?= $escape($member['Rank']) ?></div>
                        </div>
                        <div class="ld-pillar">
                            <span class="ld-points"><?= number_format((int)$member['Total_Points']) ?></span>
                            <span class="ld-points-lbl">Points</span>
                        </div>
                        <p class="ld-name"><?= $escape($member['First_Name'] . ' ' . $member['Last_Name']) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (!$podium_rankings): ?>
                    <div class="glass-card ld-empty-state">No qualifying visits have been recorded for this ranking yet.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-12" aria-labelledby="leaderboard-list-heading">
            <h3 id="leaderboard-list-heading" class="monitor-title">Rankings</h3>
            <nav class="ld-tabs" id="leaderboard-tabs" aria-label="Leaderboard period">
                <a class="ld-tab <?= $period === 'month' ? 'active' : '' ?>" href="?period=month" <?= $period === 'month' ? 'aria-current="page"' : '' ?>>This Month</a>
                <a class="ld-tab <?= $period === 'all' ? 'active' : '' ?>" href="?period=all" <?= $period === 'all' ? 'aria-current="page"' : '' ?>>All Time</a>
                <a class="ld-tab <?= $period === 'faculty' ? 'active' : '' ?>" href="?period=faculty&amp;faculty=<?= rawurlencode((string)$selected_faculty) ?>" <?= $period === 'faculty' ? 'aria-current="page"' : '' ?>>Faculty Wise</a>
            </nav>

            <?php if ($period === 'faculty'): ?>
                <form method="get" class="ld-faculty-filter">
                    <input type="hidden" name="period" value="faculty">
                    <label for="leaderboard-faculty">Faculty</label>
                    <select id="leaderboard-faculty" name="faculty" class="cal-input-field" onchange="this.form.submit()">
                        <?php foreach ($faculty_options as $faculty): ?>
                            <option value="<?= $escape($faculty) ?>" <?= $faculty === $selected_faculty ? 'selected' : '' ?>>
                                <?= $escape($faculty) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>

            <div class="glass-card ld-table-wrapper custom-scrollbar">
                <table class="ld-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Member</th>
                            <th class="hide-mobile">Faculty</th>
                            <th>Points</th>
                            <th>Qualifying visits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $member): ?>
                            <?php
                            $image = basename((string)($member['Profile_Image'] ?? ''));
                            $isCurrentMember = $member['User_ID'] === $user_id;
                            ?>
                            <tr class="<?= $isCurrentMember ? 'highlight' : '' ?>">
                                <td><?= $escape('#' . $member['Rank']) ?></td>
                                <td>
                                    <div class="ld-user-cell">
                                        <div class="ld-user-avatar">
                                            <?php if ($image !== '' && $image !== 'default_avatar.png'): ?>
                                                <img alt="" class="ld-avatar-img" src="../../assets/images/uploads/<?= $escape($image) ?>">
                                            <?php else: ?>
                                                <?= $escape($initials($member)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <span><?= $escape($member['First_Name'] . ' ' . $member['Last_Name']) ?><?= $isCurrentMember ? ' (You)' : '' ?></span>
                                    </div>
                                </td>
                                <td class="hide-mobile"><?= $escape($member['Faculty'] ?: 'Not specified') ?></td>
                                <td><?= number_format((int)$member['Total_Points']) ?></td>
                                <td><?= number_format((int)$member['Qualified_Visits']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$rankings): ?>
                            <tr><td colspan="5">No members match this leaderboard filter yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ld-widgets-grid" aria-label="Your standing and Life Bar">
            <div class="glass-card ld-standing-card">
                <div class="relative z-10">
                    <p class="mono text-xs uppercase mb-2" style="color: var(--on-surface-variant);">Your Standing</p>
                    <?php if ($current_rank): ?>
                        <h4 class="ld-big-rank">#<?= $escape($current_rank['Rank']) ?></h4>
                        <p class="text-regular-sub">
                            <?= number_format((int)$current_rank['Total_Points']) ?> points ·
                            <?= number_format((int)$current_rank['Qualified_Visits']) ?> qualifying visits
                        </p>
                    <?php else: ?>
                        <h4 class="ld-big-rank">—</h4>
                        <p class="text-regular-sub">You are not ranked in this view.</p>
                    <?php endif; ?>
                </div>
                <span class="material-symbols-outlined ld-watermark" aria-hidden="true">emoji_events</span>
            </div>

            <div class="glass-card ld-penalty-card">
                <h3 class="monitor-title">Your Life Bar</h3>
                <?php if ($life_summary): ?>
                    <p class="ld-life-value"><?= $escape((int)$life_summary['Life_Percentage']) ?>%</p>
                    <div class="progress-track" role="progressbar"
                         aria-valuemin="0" aria-valuemax="100"
                         aria-valuenow="<?= $escape((int)$life_summary['Life_Percentage']) ?>">
                        <div class="progress-fill" style="width: <?= $escape((int)$life_summary['Life_Percentage']) ?>%;"></div>
                    </div>
                <?php else: ?>
                    <p class="text-regular-sub">Life Bar information is unavailable.</p>
                <?php endif; ?>
                <h4 class="ld-penalty-heading">Recent penalties</h4>
                <?php if ($member_penalties): ?>
                    <ul class="ld-penalty-list">
                        <?php foreach ($member_penalties as $penalty): ?>
                            <?php
                            $issuer = $penalty['Issuer_Type'] === 'system'
                                ? 'System'
                                : trim((string)$penalty['Issuer_First_Name'] . ' ' . (string)$penalty['Issuer_Last_Name']);
                            ?>
                            <li>
                                <div>
                                    <strong>
                                        <?= $escape($penalty['Severity'] ?: 'Penalty') ?>
                                        <?php if ($penalty['Requested_Life_Deduction'] !== null): ?>
                                            · <?= $escape($penalty['Life_Deduction']) ?> Life lost
                                            (<?= $escape($penalty['Requested_Life_Deduction']) ?> requested)
                                        <?php else: ?>
                                            · <?= $escape($penalty['Life_Deduction']) ?> Life recorded (legacy)
                                        <?php endif; ?>
                                    </strong>
                                    <p><?= $escape($penalty['Description']) ?></p>
                                    <small>
                                        <?= $escape($penalty['Created_At']) ?>
                                        · Issued by <?= $escape($issuer !== '' ? $issuer : 'Former staff member') ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-regular-sub">No Life-Bar penalties have been recorded.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    ?>
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>
