<?php
/**
 * ============================================================
 * 公共页头组件 — includes/header.php
 * ============================================================
 * 所有页面通过 require_once 'includes/header.php' 引入
 * 传入 $pageTitle 变量可自定义浏览器标签标题
 * 编辑模式下自动追加 .editor-mode body class 与编辑工具条
 * ============================================================
 */

require_once __DIR__ . '/functions.php';

// 获取个人基本信息（全站共享）
$profile = getProfile();

// 浏览器标签标题
$pageTitle = $pageTitle ?? 'AI产品经理作品集';

// 编辑模式状态
$editing = isEditMode();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+SC:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css?v=20260714v3" />
    <?php if ($editing): ?>
    <link rel="stylesheet" href="assets/css/editor.css?v=20260714v1" />
    <?php endif; ?>
</head>
<body class="<?= $editing ? 'editor-mode' : '' ?>">
<div class="bg-noise"></div>

<?php if ($editing): ?>
<!-- 编辑模式顶部提示条 -->
<div class="edit-banner">
    编辑模式已开启 · 点击卡片右上角"编辑/删除"按钮 · 点击"+ 新增"添加内容 · 完成后请点击右下角"退出编辑"
</div>
<?php endif; ?>

<!-- ========== 顶部导航栏 ========== -->
<header class="site-header">
    <a class="brand" href="<?= e(editUrl('index.php')) ?>">
        <span class="brand-mark">AI</span>
        <span>
            <strong>AI产品经理作品集</strong>
            <small>Personal Brand Site</small>
        </span>
    </a>
    <nav class="site-nav" aria-label="主导航">
        <a href="<?= e(editUrl('index.php')) ?>">首页</a>
        <a href="<?= e(editUrl('projects.php')) ?>">作品集</a>
        <a href="<?= e(editUrl('notes.php')) ?>">学习笔记</a>
        <a href="<?= e(editUrl('about.php')) ?>">关于我</a>
        <?php if (!empty($profile['resume'])): ?>
            <a href="<?= e($profile['resume']) ?>" download>简历下载</a>
        <?php endif; ?>
    </nav>
    <div class="header-actions">
        <?php if (!empty($profile['resume'])): ?>
            <a class="icon-button" href="<?= e($profile['resume']) ?>" download title="下载简历">简历</a>
        <?php endif; ?>
    </div>
</header>

<?php if ($editing): ?>
<!-- 编辑模式浮动工具栏 -->
<div class="edit-toolbar">
    <button class="edit-tool-btn edit-tool-exit" id="edit-exit-btn" type="button">退出编辑</button>
</div>
<?php else: ?>
<!-- 进入编辑模式按钮 -->
<button class="edit-toggle-btn" id="edit-enter-btn" type="button">进入编辑</button>
<?php endif; ?>
