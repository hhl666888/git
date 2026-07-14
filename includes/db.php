<?php
/**
 * ============================================================
 * 数据库连接类 — includes/db.php
 * ============================================================
 * 作用：封装 PDO 数据库连接，全站通过 db() 函数获取连接
 * 特点：
 *   - 单例模式，避免重复连接
 *   - PDO 预处理语句，防止 SQL 注入
 *   - 异常模式，出错时自动抛出
 * ============================================================
 */

// 引入根目录的配置文件
require_once __DIR__ . '/../config.php';

/**
 * 获取数据库连接（单例）
 * 全站任何地方调用 db() 即可拿到同一个 PDO 连接
 * @return PDO
 */
function db() {
    // 静态变量保证整个请求期间只创建一次连接
    static $pdo = null;

    if ($pdo === null) {
        // 拼接 DSN（数据源名称）
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                // 抛出异常模式：出错时 throw，方便 try-catch 捕获
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // 默认返回关联数组（字段名 => 值）
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // 关闭模拟预处理，使用真正的数据库预处理
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // 连接失败时给出友好提示
            die('数据库连接失败：' . $e->getMessage() . '<br>请检查 config.php 配置是否正确。');
        }
    }

    return $pdo;
}
