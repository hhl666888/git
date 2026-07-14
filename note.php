<?php
/**
 * ============================================================
 * 笔记详情页 — note.php
 * ============================================================
 * 通过 URL 参数 ?slug=xxx 读取对应笔记详情
 * 正文支持 Markdown 渲染
 * 如果 slug 不存在或为空，显示友好提示
 * 编辑模式下：笔记详情区带"编辑"按钮
 * ============================================================
 */

require_once __DIR__ . '/includes/header.php';

// 从 URL 获取 slug 参数
$slug = $_GET['slug'] ?? '';

// 查询笔记详情
$note = null;
if ($slug !== '') {
    $note = getNoteBySlug($slug);
}

// 设置页面标题
if ($note) {
    $pageTitle = $note['title'] . ' | 学习笔记';
}

$editing = isEditMode();

// 笔记不存在时的处理
if (!$note):
?>
    <main class="shell section-block">
        <section class="section-card page-hero">
            <h1>笔记未找到</h1>
            <p>抱歉，该笔记不存在或 URL 标识有误。</p>
            <p style="margin-top: 16px;">
                <a class="button button-ghost" href="<?= e(editUrl('notes.php')) ?>">返回笔记列表</a>
            </p>
        </section>
    </main>
<?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
endif;

// 渲染 Markdown 正文为 HTML
$contentHtml = renderMarkdown($note['content']);
?>

<main class="shell section-block">
    <section class="section-card detail-shell"<?php if ($editing): ?> data-edit-type="note" data-edit-id="<?= (int)$note['id'] ?>"<?= editDataAttr($note) ?><?php endif; ?>>
        <?php if ($editing): ?><?= editCardBtns($note['id']) ?><?php endif; ?>
        <!-- 笔记头部信息 -->
        <div class="detail-head">
            <div>
                <p class="section-kicker"><?= e($note['category']) ?></p>
                <h1><?= e($note['title']) ?></h1>
                <p class="meta-line">
                    <span>发布日期：<?= e($note['date']) ?></span>
                </p>
            </div>
            <div class="detail-actions">
                <a class="button button-ghost" href="<?= e(editUrl('notes.php')) ?>">返回列表</a>
            </div>
        </div>

        <!-- 标签 -->
        <?php if (!empty($note['tags'])): ?>
            <div class="card-tags" style="margin-top: 16px;">
                <?php foreach ($note['tags'] as $tag): ?>
                    <span class="tag"><?= e($tag) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- 摘要 -->
        <?php if (!empty($note['summary'])): ?>
            <div class="toc-panel" style="margin-top: 24px;">
                <p class="section-kicker">摘要</p>
                <p><?= e($note['summary']) ?></p>
            </div>
        <?php endif; ?>

        <!-- 正文（Markdown 渲染） -->
        <div class="markdown-body" style="margin-top: 24px;">
            <?= $contentHtml ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
