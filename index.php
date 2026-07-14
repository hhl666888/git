<?php
/**
 * ============================================================
 * 首页 — index.php
 * ============================================================
 * 动态渲染：个人介绍、数据卡片、核心能力、最新笔记、代表项目
 * 所有内容来自数据库，修改数据库即可更新页面
 * 编辑模式下：每个卡片带"编辑/删除"按钮，每个区块顶部带"+ 新增"
 * ============================================================
 */

$pageTitle = 'AI产品经理作品集 | 首页';
require_once __DIR__ . '/includes/header.php';

// 从数据库读取首页需要的全部数据
$stats       = getStats();
$capabilities= getCapabilities();
$latestNotes = getLatestNotes(3);
$projects    = getProjects();
$editing     = isEditMode();
?>

<main id="home">
    <!-- ========== Hero 英雄区 ========== -->
    <section class="hero shell section-card"<?php if ($editing): ?> data-edit-type="profile" data-edit-id="1"<?= editDataAttr($profile) ?><?php endif; ?>>
        <?php if ($editing): ?><?= editCardBtns(1) ?><?php endif; ?>
        <div class="hero-copy">
            <p class="eyebrow">AI Product Manager · Portfolio · Notes · Thinking</p>
            <h1><?= e($profile['name']) ?></h1>
            <h2><?= e($profile['slogan']) ?></h2>
            <p class="hero-text"><?= e($profile['intro']) ?></p>
            <div class="hero-badges">
                <?php foreach ($profile['badges'] as $badge): ?>
                    <span class="badge"><?= e($badge) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="hero-cta">
                <a class="button button-primary" href="<?= e(editUrl('projects.php')) ?>">查看作品集</a>
                <a class="button button-ghost" href="<?= e(editUrl('notes.php')) ?>">阅读学习笔记</a>
            </div>
        </div>
        <div class="hero-panel">
            <div class="stat-grid">
                <?php foreach ($stats as $stat): ?>
                    <article class="stat-card"<?php if ($editing): ?> data-edit-type="stat" data-edit-id="<?= (int)$stat['id'] ?>"<?= editDataAttr($stat) ?><?php endif; ?>>
                        <?php if ($editing): ?><?= editCardBtns($stat['id']) ?><?php endif; ?>
                        <strong><?= e($stat['value']) ?></strong>
                        <span><?= e($stat['label']) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($editing): ?><?= editAddBtn('stat', '+ 新增数据卡片') ?><?php endif; ?>
        </div>
    </section>

    <!-- ========== 核心能力 ========== -->
    <section class="shell section-block">
        <div class="section-head">
            <div>
                <p class="section-kicker">核心能力</p>
                <h3>把 AI 产品工作的关键能力拆成可展示的模块</h3>
            </div>
        </div>
        <?php if ($editing): ?><?= editAddBtn('capability', '+ 新增能力') ?><?php endif; ?>
        <div class="capability-grid">
            <?php foreach ($capabilities as $cap): ?>
                <article class="section-card capability-card"<?php if ($editing): ?> data-edit-type="capability" data-edit-id="<?= (int)$cap['id'] ?>"<?= editDataAttr($cap) ?><?php endif; ?>>
                    <?php if ($editing): ?><?= editCardBtns($cap['id']) ?><?php endif; ?>
                    <h4><?= e($cap['title']) ?></h4>
                    <p><?= e($cap['description']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ========== 最新笔记 ========== -->
    <section class="shell section-block">
        <div class="section-head">
            <div>
                <p class="section-kicker">最新笔记</p>
                <h3>精选 3 篇，展示你的学习持续性与行业判断</h3>
            </div>
            <a href="<?= e(editUrl('notes.php')) ?>" class="text-link">查看全部</a>
        </div>
        <?php if ($editing): ?><?= editAddBtn('note', '+ 新增笔记') ?><?php endif; ?>
        <div class="card-grid">
            <?php if (empty($latestNotes)): ?>
                <article class="card"><h4>暂无笔记</h4><p>请在后台添加笔记内容。</p></article>
            <?php else: ?>
                <?php foreach ($latestNotes as $note): ?>
                    <article class="card"<?php if ($editing): ?> data-edit-type="note" data-edit-id="<?= (int)$note['id'] ?>"<?= editDataAttr($note) ?><?php endif; ?>>
                        <?php if ($editing): ?><?= editCardBtns($note['id']) ?><?php endif; ?>
                        <div class="card-header">
                            <div>
                                <h4><?= e($note['title']) ?></h4>
                                <p><?= e($note['date']) ?></p>
                            </div>
                            <span class="tag"><?= e($note['category']) ?></span>
                        </div>
                        <p><?= e($note['summary']) ?></p>
                        <div class="card-tags">
                            <?php foreach ($note['tags'] as $tag): ?>
                                <span class="badge"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer">
                            <a class="text-link" href="<?= e(editUrl('note.php?slug=' . urlencode($note['slug']))) ?>">阅读全文</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ========== 代表项目 ========== -->
    <section class="shell section-block">
        <div class="section-head">
            <div>
                <p class="section-kicker">代表项目</p>
                <h3>项目作品集首页预览</h3>
            </div>
            <a href="<?= e(editUrl('projects.php')) ?>" class="text-link">进入项目页</a>
        </div>
        <?php if ($editing): ?><?= editAddBtn('project', '+ 新增项目') ?><?php endif; ?>
        <div class="card-grid">
            <?php if (empty($projects)): ?>
                <article class="card"><h4>暂无项目</h4><p>请在后台添加项目数据。</p></article>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <article class="card"<?php if ($editing): ?> data-edit-type="project" data-edit-id="<?= (int)$project['id'] ?>"<?= editDataAttr($project) ?><?php endif; ?>>
                        <?php if ($editing): ?><?= editCardBtns($project['id']) ?><?php endif; ?>
                        <?php if (!empty($project['cover'])): ?>
                            <div class="card-cover"><?= e($project['cover']) ?></div>
                        <?php endif; ?>
                        <div class="card-header">
                            <div>
                                <h4><?= e($project['name']) ?></h4>
                                <p><?= e($project['type']) ?></p>
                            </div>
                            <a class="text-link" href="<?= e(editUrl('project.php?slug=' . urlencode($project['slug']))) ?>">查看详情</a>
                        </div>
                        <p><?= e($project['intro']) ?></p>
                        <div class="card-tags">
                            <?php foreach ($project['tags'] as $tag): ?>
                                <span class="tag"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer">
                            <span><?= e($project['role']) ?></span>
                            <strong><?= e($project['result']) ?></strong>
                        </div>
                        <?php if (!empty($project['github'])): ?>
                            <div class="card-github">
                                <a class="text-link card-github-link" href="<?= e($project['github']) ?>" target="_blank" rel="noreferrer">GitHub 仓库 ↗</a>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
