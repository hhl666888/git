<?php
/**
 * ============================================================
 * 笔记列表页 — notes.php
 * ============================================================
 * 功能：分类筛选 + 关键词搜索 + 分页
 * 参数：
 *   ?category=分类名  按分类筛选（默认"全部"）
 *   ?q=关键词         搜索标题、摘要、正文
 *   ?page=页码        分页（默认第 1 页）
 * 编辑模式下：分类侧边栏支持新增/编辑/删除，笔记卡片支持新增/编辑/删除
 * ============================================================
 */

$pageTitle = 'AI产品经理作品集 | 学习笔记';
require_once __DIR__ . '/includes/header.php';

// 从 URL 获取参数
$category = $_GET['category'] ?? '全部';
$keyword  = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 4; // 每页显示 4 篇

// 从数据库查询笔记
$categories = getNoteCategories();
$result     = getNotes($category, $keyword, $page, $perPage);
$notes      = $result['list'];
$totalPages = $result['totalPages'];
$currentPage= $result['page'];

$editing = isEditMode();

// 构建分页链接的基础参数
$baseUrl = 'notes.php?';
$params  = [];
if ($category !== '全部') $params[] = 'category=' . urlencode($category);
if ($keyword !== '')      $params[] = 'q=' . urlencode($keyword);
if ($editing)             $params[] = 'edit=1';
$paramStr = implode('&', $params);
$separator = $paramStr ? '&' : '';
?>

<main class="shell section-block notes-layout">
    <!-- ========== 左侧分类侧边栏 ========== -->
    <aside class="notes-sidebar section-card">
        <p class="section-kicker">分类标签</p>
        <div class="filter-list">
            <!-- "全部" 按钮 -->
            <button class="filter-pill <?= $category === '全部' ? 'active' : '' ?>"
                    onclick="location.href='notes.php?<?= $keyword !== '' ? 'q=' . urlencode($keyword) : '' ?><?= $editing ? '&edit=1' : '' ?>'"
                    type="button">全部</button>

            <!-- 各分类按钮 -->
            <?php foreach ($categories as $cat): ?>
                <?php if ($editing): ?>
                    <!-- 编辑模式：filter-pill 整体可点击进入编辑 -->
                    <span class="filter-pill <?= $category === $cat['name'] ? 'active' : '' ?>"
                          data-edit-type="category"
                          data-edit-id="<?= (int)$cat['id'] ?>"
                          <?= editDataAttr($cat) ?>>
                        <?= e($cat['name']) ?>
                        <span class="filter-del"
                              data-edit-action="delete"
                              title="删除分类">×</span>
                    </span>
                <?php else: ?>
                    <button class="filter-pill <?= $category === $cat['name'] ? 'active' : '' ?>"
                            onclick="location.href='notes.php?category=<?= urlencode($cat['name']) ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?>'"
                            type="button"><?= e($cat['name']) ?></button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php if ($editing): ?><?= editAddBtn('category', '+ 新增分类') ?><?php endif; ?>
    </aside>

    <!-- ========== 右侧笔记主区域 ========== -->
    <section class="notes-main">
        <!-- 搜索区 -->
        <div class="section-card search-card">
            <p class="section-kicker">学习笔记</p>
            <h1>笔记专栏</h1>
            <p>支持标题与正文模糊搜索、标签筛选、分页展示与 Markdown 渲染。</p>
            <form class="search-box" method="GET" action="notes.php">
                <?php if ($category !== '全部'): ?>
                    <input type="hidden" name="category" value="<?= e($category) ?>" />
                <?php endif; ?>
                <?php if ($editing): ?>
                    <input type="hidden" name="edit" value="1" />
                <?php endif; ?>
                <span>搜索</span>
                <input type="search" name="q" value="<?= e($keyword) ?>"
                       placeholder="搜索标题、摘要或正文关键词" />
            </form>
        </div>

        <!-- 笔记列表 -->
        <?php if ($editing): ?><?= editAddBtn('note', '+ 新增笔记') ?><?php endif; ?>
        <div class="card-grid note-list-grid">
            <?php if (empty($notes)): ?>
                <!-- 搜索/筛选结果为空时的友好提示 -->
                <article class="card">
                    <h4>暂无匹配内容</h4>
                    <p>请尝试切换分类或使用其他关键词搜索。</p>
                </article>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
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
                            <a class="text-link" href="<?= e(editUrl('note.php?slug=' . urlencode($note['slug']))) ?>">打开笔记</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 分页导航 -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <!-- 上一页 -->
                <?php if ($currentPage > 1): ?>
                    <a class="page-btn" href="notes.php?<?= $paramStr ?><?= $separator ?>page=<?= $currentPage - 1 ?>">上一页</a>
                <?php endif; ?>

                <!-- 页码按钮 -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a class="page-btn <?= $i === $currentPage ? 'active' : '' ?>"
                       href="notes.php?<?= $paramStr ?><?= $separator ?>page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>

                <!-- 下一页 -->
                <?php if ($currentPage < $totalPages): ?>
                    <a class="page-btn" href="notes.php?<?= $paramStr ?><?= $separator ?>page=<?= $currentPage + 1 ?>">下一页</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
