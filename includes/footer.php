<?php
/**
 * ============================================================
 * 公共页脚组件 — includes/footer.php
 * ============================================================
 * 所有页面通过 require_once 'includes/footer.php' 引入
 * 自动读取数据库中的姓名、GitHub、邮箱
 * 在编辑模式下引入 editor.js
 * ============================================================
 */
$editing = isEditMode();
?>
<footer class="site-footer shell">
    <div>
        <strong><?= e($profile['name'] ?? '') ?></strong>
        <p>AI 产品经理个人品牌官网 · 简约科技风 · 轻量化静态站点</p>
    </div>
    <div class="footer-links">
        <?php if (!empty($profile['github'])): ?>
            <a href="<?= e($profile['github']) ?>" target="_blank" rel="noreferrer">GitHub</a>
        <?php endif; ?>
        <?php if (!empty($profile['email'])): ?>
            <a href="mailto:<?= e($profile['email']) ?>">邮箱</a>
        <?php endif; ?>
        <a href="<?= e(editUrl('about.php')) ?>">关于我</a>
    </div>
</footer>

<?php if (!empty($profile['resume'])): ?>
<div class="floating-actions">
    <a class="floating-button" href="<?= e($profile['resume']) ?>" download>下载简历 PDF</a>
</div>
<?php endif; ?>

<?php if ($editing): ?>
<!-- 编辑模式：注入笔记分类数据 -->
<script>
window.__NOTE_CATEGORIES__ = <?php
    $cats = getNoteCategories();
    echo json_encode($cats, JSON_UNESCAPED_UNICODE);
?>;
</script>
<?php endif; ?>
<!-- 编辑器脚本（始终加载，处理进入/退出编辑模式及编辑操作） -->
<script src="assets/js/editor.js?v=20260714v4"></script>
</body>
</html>
