<?php
/**
 * ============================================================
 * 编辑接口 — api.php
 * ============================================================
 * 作用：接收前端 AJAX 请求，对数据库进行增删改操作
 * 协议：仅接受 POST + JSON 请求体，返回 JSON 响应
 *
 * 调用方式：
 *   POST api.php
 *   Content-Type: application/json
 *   Body: { "action": "edit_note", "id": 5, "data": {...} }
 *
 * 响应：
 *   成功: { "ok": true,  "data": { ... } }
 *   失败: { "ok": false, "error": "错误说明" }
 * ============================================================
 */

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// 仅接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => '仅支持 POST 请求']);
    exit;
}

// 读取 JSON 请求体
$rawInput = file_get_contents('php://input');
$input    = json_decode($rawInput, true);
if (!is_array($input)) {
    // 兼容 form-data 提交
    $input = $_POST;
}
$action = $input['action'] ?? '';
$id     = isset($input['id']) ? (int)$input['id'] : 0;
$data   = $input['data'] ?? [];

/**
 * 路由表：action => 处理函数名
 * 函数签名: function(array $data, int $id): array
 */
$routes = [
    // 编辑模式切换
    'set_edit_mode'    => 'handleSetEditMode',

    // 个人信息（单行配置）
    'update_profile'   => 'handleUpdateProfile',

    // 关于我（单行配置）
    'update_about'     => 'handleUpdateAbout',

    // 首页数据卡片
    'add_stat'         => 'handleAddStat',
    'edit_stat'        => 'handleEditStat',
    'delete_stat'      => 'handleDeleteStat',

    // 核心能力
    'add_capability'   => 'handleAddCapability',
    'edit_capability'  => 'handleEditCapability',
    'delete_capability'=> 'handleDeleteCapability',

    // 项目
    'add_project'      => 'handleAddProject',
    'edit_project'     => 'handleEditProject',
    'delete_project'   => 'handleDeleteProject',

    // 笔记
    'add_note'         => 'handleAddNote',
    'edit_note'        => 'handleEditNote',
    'delete_note'      => 'handleDeleteNote',

    // 笔记分类
    'add_category'     => 'handleAddCategory',
    'edit_category'    => 'handleEditCategory',
    'delete_category'  => 'handleDeleteCategory',

    // 教育经历
    'add_education'    => 'handleAddEducation',
    'edit_education'   => 'handleEditEducation',
    'delete_education' => 'handleDeleteEducation',

    // 工作经历
    'add_work'         => 'handleAddWork',
    'edit_work'        => 'handleEditWork',
    'delete_work'      => 'handleDeleteWork',
];

if (!isset($routes[$action])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => '未知 action: ' . $action]);
    exit;
}

try {
    $result = $routes[$action]($data, $id);
    echo json_encode(['ok' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
exit;


// ============================================================
//  辅助函数
// ============================================================

/**
 * 把数组编码为 JSON 字符串（数据库 JSON 字段用）
 */
function toJson($arr): string {
    return is_array($arr) ? json_encode(array_values($arr), JSON_UNESCAPED_UNICODE) : '[]';
}

/**
 * 取出 data 中的字段，不存在则用默认值
 */
function field(array $data, string $key, $default = '') {
    return $data[$key] ?? $default;
}


// ============================================================
//  编辑模式切换
// ============================================================

function handleSetEditMode(array $data, int $id): array {
    $mode = !empty($data['mode']);
    if ($mode) {
        setcookie('edit_mode', '1', time() + 86400 * 365 * 100, '/');
    } else {
        setcookie('edit_mode', '', time() - 3600, '/');
    }
    return ['mode' => $mode];
}


// ============================================================
//  个人信息
// ============================================================

function handleUpdateProfile(array $data, int $id): array {
    $stmt = db()->prepare('
        UPDATE site_profile SET
            name=?, slogan=?, intro=?, github=?, email=?, resume=?, badges=?
        WHERE id=1
    ');
    $stmt->execute([
        field($data, 'name'),
        field($data, 'slogan'),
        field($data, 'intro'),
        field($data, 'github'),
        field($data, 'email'),
        field($data, 'resume', 'assets/files/resume.pdf'),
        toJson($data['badges'] ?? []),
    ]);
    return ['updated' => true];
}


// ============================================================
//  关于我
// ============================================================

function handleUpdateAbout(array $data, int $id): array {
    $stmt = db()->prepare('UPDATE about_info SET plan=?, evaluation=?, tools=? WHERE id=1');
    $stmt->execute([
        field($data, 'plan'),
        field($data, 'evaluation'),
        toJson($data['tools'] ?? []),
    ]);
    return ['updated' => true];
}


// ============================================================
//  首页数据卡片
// ============================================================

function handleAddStat(array $data, int $id): array {
    $stmt = db()->prepare('INSERT INTO stats (value, label, sort_order) VALUES (?, ?, ?)');
    $stmt->execute([
        field($data, 'value'),
        field($data, 'label'),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditStat(array $data, int $id): array {
    $stmt = db()->prepare('UPDATE stats SET value=?, label=?, sort_order=? WHERE id=?');
    $stmt->execute([
        field($data, 'value'),
        field($data, 'label'),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteStat(array $data, int $id): array {
    $stmt = db()->prepare('DELETE FROM stats WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}


// ============================================================
//  核心能力
// ============================================================

function handleAddCapability(array $data, int $id): array {
    $stmt = db()->prepare('INSERT INTO capabilities (title, description, sort_order) VALUES (?, ?, ?)');
    $stmt->execute([
        field($data, 'title'),
        field($data, 'description'),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditCapability(array $data, int $id): array {
    $stmt = db()->prepare('UPDATE capabilities SET title=?, description=?, sort_order=? WHERE id=?');
    $stmt->execute([
        field($data, 'title'),
        field($data, 'description'),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteCapability(array $data, int $id): array {
    $stmt = db()->prepare('DELETE FROM capabilities WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}


// ============================================================
//  项目
// ============================================================

function handleAddProject(array $data, int $id): array {
    $stmt = db()->prepare('
        INSERT INTO projects
            (slug, name, type, intro, role, result, cover, github, tags,
             detail_background, detail_prd, detail_screenshots, detail_metrics,
             detail_review, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        field($data, 'slug'),
        field($data, 'name'),
        field($data, 'type'),
        field($data, 'intro'),
        field($data, 'role'),
        field($data, 'result'),
        field($data, 'cover'),
        field($data, 'github'),
        toJson($data['tags'] ?? []),
        field($data, 'detail_background'),
        toJson($data['detail_prd'] ?? []),
        toJson($data['detail_screenshots'] ?? []),
        toJson($data['detail_metrics'] ?? []),
        field($data, 'detail_review'),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditProject(array $data, int $id): array {
    $stmt = db()->prepare('
        UPDATE projects SET
            slug=?, name=?, type=?, intro=?, role=?, result=?, cover=?, github=?, tags=?,
            detail_background=?, detail_prd=?, detail_screenshots=?, detail_metrics=?,
            detail_review=?, sort_order=?
        WHERE id=?
    ');
    $stmt->execute([
        field($data, 'slug'),
        field($data, 'name'),
        field($data, 'type'),
        field($data, 'intro'),
        field($data, 'role'),
        field($data, 'result'),
        field($data, 'cover'),
        field($data, 'github'),
        toJson($data['tags'] ?? []),
        field($data, 'detail_background'),
        toJson($data['detail_prd'] ?? []),
        toJson($data['detail_screenshots'] ?? []),
        toJson($data['detail_metrics'] ?? []),
        field($data, 'detail_review'),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteProject(array $data, int $id): array {
    $stmt = db()->prepare('DELETE FROM projects WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}


// ============================================================
//  笔记
// ============================================================

function handleAddNote(array $data, int $id): array {
    // 校验分类 ID 有效性
    $categoryId = (int)field($data, 'category_id', 0);
    if ($categoryId <= 0) {
        throw new Exception('请选择笔记分类');
    }
    $catStmt = db()->prepare('SELECT id FROM note_categories WHERE id = ?');
    $catStmt->execute([$categoryId]);
    if (!$catStmt->fetch()) {
        throw new Exception('所选分类不存在，请先创建分类');
    }

    $stmt = db()->prepare('
        INSERT INTO notes
            (slug, title, date, category_id, summary, content, tags, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        field($data, 'slug'),
        field($data, 'title'),
        field($data, 'date', date('Y-m-d')),
        $categoryId,
        field($data, 'summary'),
        field($data, 'content'),
        toJson($data['tags'] ?? []),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditNote(array $data, int $id): array {
    // 校验分类 ID 有效性
    $categoryId = (int)field($data, 'category_id', 0);
    if ($categoryId <= 0) {
        throw new Exception('请选择笔记分类');
    }
    $catStmt = db()->prepare('SELECT id FROM note_categories WHERE id = ?');
    $catStmt->execute([$categoryId]);
    if (!$catStmt->fetch()) {
        throw new Exception('所选分类不存在，请先创建分类');
    }

    $stmt = db()->prepare('
        UPDATE notes SET
            slug=?, title=?, date=?, category_id=?, summary=?, content=?, tags=?, sort_order=?
        WHERE id=?
    ');
    $stmt->execute([
        field($data, 'slug'),
        field($data, 'title'),
        field($data, 'date'),
        $categoryId,
        field($data, 'summary'),
        field($data, 'content'),
        toJson($data['tags'] ?? []),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteNote(array $data, int $id): array {
    $stmt = db()->prepare('DELETE FROM notes WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}


// ============================================================
//  笔记分类
// ============================================================

function handleAddCategory(array $data, int $id): array {
    $stmt = db()->prepare('INSERT INTO note_categories (name, sort_order) VALUES (?, ?)');
    $stmt->execute([
        field($data, 'name'),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditCategory(array $data, int $id): array {
    $stmt = db()->prepare('UPDATE note_categories SET name=?, sort_order=? WHERE id=?');
    $stmt->execute([
        field($data, 'name'),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteCategory(array $data, int $id): array {
    // 检查是否还有笔记使用此分类
    $check = db()->prepare('SELECT COUNT(*) FROM notes WHERE category_id=?');
    $check->execute([$id]);
    if ((int)$check->fetchColumn() > 0) {
        throw new RuntimeException('该分类下还有笔记，无法删除。请先迁移笔记到其他分类。');
    }
    $stmt = db()->prepare('DELETE FROM note_categories WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}


// ============================================================
//  教育经历
// ============================================================

function handleAddEducation(array $data, int $id): array {
    $stmt = db()->prepare('INSERT INTO about_education (content, sort_order) VALUES (?, ?)');
    $stmt->execute([
        field($data, 'content'),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditEducation(array $data, int $id): array {
    $stmt = db()->prepare('UPDATE about_education SET content=?, sort_order=? WHERE id=?');
    $stmt->execute([
        field($data, 'content'),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteEducation(array $data, int $id): array {
    $stmt = db()->prepare('DELETE FROM about_education WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}


// ============================================================
//  工作经历
// ============================================================

function handleAddWork(array $data, int $id): array {
    $stmt = db()->prepare('INSERT INTO about_work (content, sort_order) VALUES (?, ?)');
    $stmt->execute([
        field($data, 'content'),
        (int)field($data, 'sort_order', 0),
    ]);
    return ['id' => (int)db()->lastInsertId()];
}

function handleEditWork(array $data, int $id): array {
    $stmt = db()->prepare('UPDATE about_work SET content=?, sort_order=? WHERE id=?');
    $stmt->execute([
        field($data, 'content'),
        (int)field($data, 'sort_order', 0),
        $id,
    ]);
    return ['updated' => true];
}

function handleDeleteWork(array $data, int $id): array {
    $stmt = db()->prepare('DELETE FROM about_work WHERE id=?');
    $stmt->execute([$id]);
    return ['deleted' => true];
}
