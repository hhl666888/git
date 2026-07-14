<?php
/**
 * ============================================================
 * 公共函数库 — includes/functions.php
 * ============================================================
 * 包含：
 *   - 数据查询函数（从数据库读取各板块数据）
 *   - HTML 转义函数（防止 XSS）
 *   - 分页函数（笔记列表分页）
 *   - Markdown 渲染函数（笔记正文）
 * ============================================================
 */

require_once __DIR__ . '/db.php';

/**
 * HTML 转义输出（防止 XSS 跨站脚本攻击）
 * 所有从数据库读出来、要输出到页面的文本，都应该用这个函数过滤
 * @param string $text 原始文本
 * @return string 转义后的安全文本
 */
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 把 JSON 字符串解析为数组
 * 数据库中 tags 等字段存的是 JSON，读取后需要转成 PHP 数组才能遍历
 * @param string|null $jsonStr JSON 字符串
 * @return array 解析后的数组，解析失败返回空数组
 */
function parseJsonArray($jsonStr) {
    if (empty($jsonStr)) return [];
    $arr = json_decode($jsonStr, true);
    return is_array($arr) ? $arr : [];
}

// ============================================================
//  编辑模式辅助函数
// ============================================================

/**
 * 是否处于编辑模式
 * 通过 cookie 持久化，跨页面保持状态
 * @return bool
 */
function isEditMode() {
    // URL 参数 ?edit=1 优先（用于显式进入）
    if (isset($_GET['edit'])) {
        return $_GET['edit'] === '1';
    }
    // 否则读取 cookie
    return isset($_COOKIE['edit_mode']) && $_COOKIE['edit_mode'] === '1';
}

/**
 * 输出 body 的 class（编辑模式时附加 editor-mode）
 * 用于 PHP 页面 <body class="..."> 标签
 * @return string
 */
function bodyClass() {
    return isEditMode() ? 'editor-mode' : '';
}

/**
 * 在编辑模式下，给 URL 自动追加 ?edit=1，保持跨页面状态
 * 用于导航链接、按钮跳转等
 * @param string $url 原始 URL
 * @return string 拼接后的 URL
 */
function editUrl($url) {
    if (!isEditMode()) return $url;
    // 已有 query string 则追加，否则新增
    $sep = strpos($url, '?') !== false ? '&' : '?';
    // 避免重复追加
    if (strpos($url, 'edit=1') !== false) return $url;
    return $url . $sep . 'edit=1';
}

/**
 * 渲染卡片右上角的编辑/删除按钮（仅在编辑模式下输出）
 * @param int    $id    实体 ID
 * @return string HTML（非编辑模式返回空字符串）
 */
function editCardBtns($id) {
    if (!isEditMode()) return '';
    return '<div class="edit-card-btns">'
        . '<button class="edit-card-btn" data-edit-action="edit" type="button">编辑</button>'
        . '<button class="edit-card-btn edit-card-del" data-edit-action="delete" type="button">删除</button>'
        . '</div>';
}

/**
 * 渲染"+ 新增"按钮（仅在编辑模式下输出）
 * @param string $type 实体类型
 * @param string $label 按钮文案
 * @return string HTML
 */
function editAddBtn($type, $label = '+ 新增') {
    if (!isEditMode()) return '';
    return '<button class="edit-add-btn" data-edit-action="add" data-edit-type="' . e($type) . '" type="button">' . e($label) . '</button>';
}

/**
 * 把数组/对象编码为 data-edit-data 属性值
 * 用于把 PHP 数据传给前端 JS
 * @param mixed $data
 * @return string  HTML 属性字符串（含前导空格）
 */
function editDataAttr($data) {
    if (!isEditMode()) return '';
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
    return ' data-edit-data="' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '"';
}

// ============================================================
//  数据查询函数 —— 每个函数对应一个页面板块
// ============================================================

/**
 * 获取个人基本信息（全站共享）
 * @return array|null
 */
function getProfile() {
    $stmt = db()->prepare('SELECT * FROM site_profile WHERE id = 1 LIMIT 1');
    $stmt->execute();
    $profile = $stmt->fetch();
    if ($profile) {
        $profile['badges'] = parseJsonArray($profile['badges'] ?? null);
    }
    return $profile ?: null;
}

/**
 * 获取首页数据卡片
 * @return array
 */
function getStats() {
    $stmt = db()->prepare('SELECT id, value, label, sort_order FROM stats ORDER BY sort_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * 获取核心能力卡片
 * @return array
 */
function getCapabilities() {
    $stmt = db()->prepare('SELECT id, title, description, sort_order FROM capabilities ORDER BY sort_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * 获取所有项目（列表用，不含详情字段）
 * @return array
 */
function getProjects() {
    $stmt = db()->prepare('
        SELECT id, slug, name, type, intro, role, result, cover, github, tags, sort_order
        FROM projects
        ORDER BY sort_order ASC
    ');
    $stmt->execute();
    $projects = $stmt->fetchAll();
    // 把每个项目的 tags 从 JSON 字符串转成数组
    foreach ($projects as &$p) {
        $p['tags'] = parseJsonArray($p['tags'] ?? null);
    }
    return $projects;
}

/**
 * 根据 slug 获取单个项目详情
 * @param string $slug URL 标识
 * @return array|null
 */
function getProjectBySlug($slug) {
    $stmt = db()->prepare('SELECT * FROM projects WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $project = $stmt->fetch();
    if ($project) {
        $project['tags']               = parseJsonArray($project['tags'] ?? null);
        $project['detail_prd']         = parseJsonArray($project['detail_prd'] ?? null);
        $project['detail_screenshots'] = parseJsonArray($project['detail_screenshots'] ?? null);
        $project['detail_metrics']     = parseJsonArray($project['detail_metrics'] ?? null);
    }
    return $project ?: null;
}

/**
 * 获取笔记分类列表
 * @return array
 */
function getNoteCategories() {
    $stmt = db()->prepare('SELECT id, name, sort_order FROM note_categories ORDER BY sort_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * 获取笔记列表（支持按分类筛选 + 关键词搜索 + 分页）
 * @param string $category 分类名（"全部" 表示不筛选）
 * @param string $keyword  搜索关键词（为空则不搜索）
 * @param int    $page     当前页码（从 1 开始）
 * @param int    $perPage  每页显示条数
 * @return array ['list' => 笔记数组, 'total' => 总条数, 'totalPages' => 总页数]
 */
function getNotes($category = '全部', $keyword = '', $page = 1, $perPage = 4) {
    // 构建 WHERE 条件
    $where  = '';
    $params = [];

    // 按分类筛选
    if ($category !== '全部' && $category !== '') {
        $where  .= ' AND nc.name = ?';
        $params[] = $category;
    }

    // 关键词搜索（在标题、摘要、正文中匹配）
    if ($keyword !== '') {
        $where  .= ' AND (n.title LIKE ? OR n.summary LIKE ? OR n.content LIKE ?)';
        $like    = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // 先查总条数（用于分页）
    $countSql = "SELECT COUNT(*) FROM notes n
                 JOIN note_categories nc ON n.category_id = nc.id
                 WHERE 1=1 $where";
    $stmt = db()->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $perPage));

    // 页码边界保护：不能小于 1，不能大于总页数
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    // 查当前页数据
    $listSql = "SELECT n.id, n.slug, n.title, n.date, n.category_id, nc.name AS category,
                       n.summary, n.tags, n.sort_order
                FROM notes n
                JOIN note_categories nc ON n.category_id = nc.id
                WHERE 1=1 $where
                ORDER BY n.date DESC, n.sort_order ASC
                LIMIT $perPage OFFSET $offset";
    $stmt = db()->prepare($listSql);
    $stmt->execute($params);
    $list = $stmt->fetchAll();

    // tags JSON 转数组
    foreach ($list as &$n) {
        $n['tags'] = parseJsonArray($n['tags'] ?? null);
    }

    return [
        'list'       => $list,
        'total'      => $total,
        'totalPages' => $totalPages,
        'page'       => $page,
    ];
}

/**
 * 根据 slug 获取单篇笔记详情
 * @param string $slug
 * @return array|null
 */
function getNoteBySlug($slug) {
    $stmt = db()->prepare('
        SELECT n.*, nc.name AS category
        FROM notes n
        JOIN note_categories nc ON n.category_id = nc.id
        WHERE n.slug = ?
        LIMIT 1
    ');
    $stmt->execute([$slug]);
    $note = $stmt->fetch();
    if ($note) {
        $note['tags'] = parseJsonArray($note['tags'] ?? null);
    }
    return $note ?: null;
}

/**
 * 获取最新 N 篇笔记（首页用）
 * @param int $limit
 * @return array
 */
function getLatestNotes($limit = 3) {
    $stmt = db()->prepare('
        SELECT n.id, n.slug, n.title, n.date, n.category_id, nc.name AS category, n.summary, n.tags
        FROM notes n
        JOIN note_categories nc ON n.category_id = nc.id
        ORDER BY n.date DESC
        LIMIT ?
    ');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $list = $stmt->fetchAll();
    foreach ($list as &$n) {
        $n['tags'] = parseJsonArray($n['tags'] ?? null);
    }
    return $list;
}

/**
 * 获取关于我页面全部数据
 * @return array
 */
function getAboutData() {
    // 基础配置
    $stmt = db()->prepare('SELECT * FROM about_info WHERE id = 1 LIMIT 1');
    $stmt->execute();
    $info = $stmt->fetch() ?: ['plan' => '', 'evaluation' => '', 'tools' => '[]'];
    $info['tools'] = parseJsonArray($info['tools'] ?? null);

    // 教育经历
    $stmt = db()->prepare('SELECT id, content, sort_order FROM about_education ORDER BY sort_order ASC');
    $stmt->execute();
    $education = $stmt->fetchAll();

    // 工作经历
    $stmt = db()->prepare('SELECT id, content, sort_order FROM about_work ORDER BY sort_order ASC');
    $stmt->execute();
    $work = $stmt->fetchAll();

    return [
        'plan'      => $info['plan'],
        'evaluation'=> $info['evaluation'],
        'tools'     => $info['tools'],
        'education' => $education,
        'work'      => $work,
    ];
}

// ============================================================
//  Markdown 渲染（轻量版，支持标题、列表、表格、引用、代码、粗体）
// ============================================================

/**
 * 将 Markdown 文本渲染为 HTML
 * 这是一个轻量实现，不依赖第三方库
 * 如果需要更完整的渲染，建议安装 Parsedown 库
 * @param string $markdown Markdown 原文
 * @return string HTML
 */
function renderMarkdown($markdown) {
    if (empty($markdown)) return '';

    // 先转义 HTML，防止 XSS
    $html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');

    // 代码块（```...```）
    $html = preg_replace_callback(
        '/```(\w*)\n(.*?)```/s',
        function ($m) {
            return '<pre><code>' . trim($m[2]) . '</code></pre>';
        },
        $html
    );

    // 行内代码 `code`
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

    // 表格（简单的 Markdown 表格解析）
    $html = preg_replace_callback(
        '/((?:\|[^\n]+\n)+)/',
        function ($m) {
            $lines = explode("\n", trim($m[1]));
            if (count($lines) < 2) return $m[1];
            // 第二行是分隔线 |---|---|
            $rows = [];
            foreach ($lines as $i => $line) {
                if (preg_match('/^\|[\s\-:|]+\|$/', $line)) continue; // 跳过分隔线
                $cells = array_map('trim', explode('|', trim($line, '|')));
                $rows[] = $cells;
            }
            if (empty($rows)) return $m[1];
            $table = '<table>';
            // 第一行是表头
            $table .= '<thead><tr>';
            foreach ($rows[0] as $cell) $table .= '<th>' . $cell . '</th>';
            $table .= '</tr></thead><tbody>';
            // 后续行是数据
            for ($i = 1; $i < count($rows); $i++) {
                $table .= '<tr>';
                foreach ($rows[$i] as $cell) $table .= '<td>' . $cell . '</td>';
                $table .= '</tr>';
            }
            $table .= '</tbody></table>';
            return $table;
        },
        $html
    );

    // 标题 h1~h4
    $html = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $html);
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

    // 引用块 >
    $html = preg_replace('/^&gt; (.+)$/m', '<blockquote>$1</blockquote>', $html);

    // 粗体 **text**
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

    // 无序列表 - 或 *
    $html = preg_replace_callback(
        '/((?:^[-*] .+\n?)+)/m',
        function ($m) {
            $items = '';
            foreach (explode("\n", trim($m[1])) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $item = preg_replace('/^[-*] /', '', $line);
                $items .= '<li>' . $item . '</li>';
            }
            return '<ul>' . $items . '</ul>';
        },
        $html
    );

    // 有序列表 1. 2. 3.
    $html = preg_replace_callback(
        '/((?:^\d+\. .+\n?)+)/m',
        function ($m) {
            $items = '';
            foreach (explode("\n", trim($m[1])) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $item = preg_replace('/^\d+\. /', '', $line);
                $items .= '<li>' . $item . '</li>';
            }
            return '<ol>' . $items . '</ol>';
        },
        $html
    );

    // 段落（连续两个换行符分隔）
    $paragraphs = preg_split('/\n{2,}/', $html);
    $result = '';
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if ($p === '') continue;
        // 如果已经是块级元素，不再包 p 标签
        if (preg_match('/^<(h[1-4]|ul|ol|pre|blockquote|table)/', $p)) {
            $result .= $p . "\n";
        } else {
            // 单换行转 <br>
            $p = preg_replace('/\n/', "<br>\n", $p);
            $result .= '<p>' . $p . "</p>\n";
        }
    }

    return $result;
}
