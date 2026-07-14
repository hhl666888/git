<?php
/**
 * ============================================================
 * 项目列表页 — projects.php
 * ============================================================
 * 从数据库读取所有项目并渲染为卡片列表
 * 编辑模式下：每个卡片带"编辑/删除"按钮，顶部带"+ 新增"
 * ============================================================
 */

$pageTitle = 'AI产品经理作品集 | 项目';
require_once __DIR__ . '/includes/header.php';

$projects = getProjects();
$editing  = isEditMode();
?>

<main class="shell section-block">
    <!-- 页面标题区 -->
    <section class="section-card page-hero">
        <p class="section-kicker">作品集</p>
        <h1>项目作品</h1>
        <p>展示 AI 产品经理在 RAG、Prompt 工程、多模态等方向的项目实践与成果。</p>
    </section>

    <!-- 项目卡片列表 -->
    <?php if ($editing): ?><?= editAddBtn('project', '+ 新增项目') ?><?php endif; ?>
    <div class="card-grid">
        <?php if (empty($projects)): ?>
            <article class="card">
                <h4>暂无项目数据</h4>
                <p>请先在数据库 projects 表中添加项目记录。</p>
            </article>
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
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
