<?php
/**
 * ============================================================
 * 项目详情页 — project.php
 * ============================================================
 * 通过 URL 参数 ?slug=xxx 读取对应项目详情
 * 如果 slug 不存在或为空，显示友好提示
 * 编辑模式下：项目详情区带"编辑"按钮
 * ============================================================
 */

$pageTitle = 'AI产品经理作品集 | 项目详情';
require_once __DIR__ . '/includes/header.php';

// 从 URL 获取 slug 参数
$slug = $_GET['slug'] ?? '';

// 查询项目详情
$project = null;
if ($slug !== '') {
    $project = getProjectBySlug($slug);
}

$editing = isEditMode();

// 项目不存在时的处理
if (!$project):
?>
    <main class="shell section-block">
        <section class="section-card page-hero">
            <h1>项目未找到</h1>
            <p>抱歉，该项目不存在或 URL 标识有误。</p>
            <p style="margin-top: 16px;">
                <a class="button button-ghost" href="<?= e(editUrl('projects.php')) ?>">返回项目列表</a>
            </p>
        </section>
    </main>
<?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
endif;
?>

<main class="shell section-block">
    <!-- 项目详情头部 -->
    <section class="section-card detail-shell"<?php if ($editing): ?> data-edit-type="project" data-edit-id="<?= (int)$project['id'] ?>"<?= editDataAttr($project) ?><?php endif; ?>>
        <?php if ($editing): ?><?= editCardBtns($project['id']) ?><?php endif; ?>
        <div class="detail-head">
            <div>
                <p class="section-kicker"><?= e($project['type']) ?></p>
                <h1><?= e($project['name']) ?></h1>
                <p class="meta-line">
                    <span>职责：<?= e($project['role']) ?></span>
                    <span>成果：<?= e($project['result']) ?></span>
                </p>
            </div>
            <div class="detail-actions">
                <?php if (!empty($project['github'])): ?>
                    <a class="button button-primary" href="<?= e($project['github']) ?>" target="_blank" rel="noreferrer">GitHub 仓库</a>
                <?php endif; ?>
                <a class="button button-ghost" href="<?= e(editUrl('projects.php')) ?>">返回列表</a>
            </div>
        </div>

        <div class="card-tags" style="margin-top: 16px;">
            <?php foreach ($project['tags'] as $tag): ?>
                <span class="tag"><?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>

        <!-- 项目详情内容 -->
        <div class="detail-content" style="margin-top: 24px;">
            <p><?= e($project['intro']) ?></p>

            <?php if (!empty($project['detail_background'])): ?>
                <h2>项目背景</h2>
                <p><?= e($project['detail_background']) ?></p>
            <?php endif; ?>

            <?php if (!empty($project['detail_prd'])): ?>
                <h2>PRD 要点</h2>
                <ul>
                    <?php foreach ($project['detail_prd'] as $prd): ?>
                        <li><?= e($prd) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($project['detail_screenshots'])): ?>
                <h2>原型截图</h2>
                <div class="card-tags">
                    <?php foreach ($project['detail_screenshots'] as $shot): ?>
                        <span class="badge"><?= e($shot) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['detail_metrics'])): ?>
                <h2>核心指标</h2>
                <div class="card-tags">
                    <?php foreach ($project['detail_metrics'] as $metric): ?>
                        <span class="badge"><?= e($metric) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['detail_review'])): ?>
                <h2>复盘总结</h2>
                <blockquote><?= e($project['detail_review']) ?></blockquote>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
