<?php
/**
 * sitemap.php
 *
 * 生成 XML sitemap。放在主题根目录，通过
 *   https://example.com/usr/themes/VOID/sitemap.php
 * 访问，再在 robots.txt 中声明。
 *
 * 如需自定义 URL 前缀，请修改下方 SITE_URL 常量，
 * 或改为从 Typecho 配置动态读取（见文件末尾注释）。
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    // 允许直接以 CLI 方式生成，或由前端控制器引入
    exit;
}

/* ------------------------------------------------------------------ *
 * 站点地址：优先用 Typecho 配置，未初始化时回落到环境变量或硬编码。
 * ------------------------------------------------------------------ */
$sitemapSiteUrl = rtrim((string) Helper::options()->siteUrl, '/') . '/';

// 支持查询参数指定输出范围：
//   sitemap.php?type=post     仅文章
//   sitemap.php?type=page     仅独立页面
//   sitemap.php?type=all      全部（默认）
//   sitemap.php?days=30      最近 30 天修改过的内容（便于搜索引擎快速刷新）
$sitemapType = isset($_GET['type']) ? (string) $_GET['type'] : 'all';
$sitemapDays = isset($_GET['days']) ? (int) $_GET['days'] : 0;

/* ------------------------------------------------------------------ *
 * 组装查询
 * ------------------------------------------------------------------ */
$db = Db::get();

$selectParts = array(
    'table'      => TypechoDb::getTable('contents'),
    'fields'     => array('cid', 'title', 'slug', 'type', 'created', 'modified', 'status'),
);

$whereParts = array(
    'table'  => TypechoDb::getTable('contents'),
    'where'  => 'status = ? AND created <= ?',
    'params' => array(
        $db->quote('publish'),
        time(),
    ),
);

switch ($sitemapType) {
    case 'post':
        $whereParts['where'] .= ' AND type = ?';
        $whereParts['params'][] = $db->quote('post');
        break;

    case 'page':
        $whereParts['where'] .= ' AND type = ?';
        $whereParts['params'][] = $db->quote('page');
        break;

    case 'all':
    default:
        // 文章与独立页面都要
        $whereParts['where'] .= ' AND type IN (?, ?)';
        $whereParts['params'][] = $db->quote('post');
        $whereParts['params'][] = $db->quote('page');
        break;
}

if ($sitemapDays > 0) {
    $whereParts['where'] .= ' AND modified >= ?';
    $whereParts['params'][] = time() - ($sitemapDays * 86400);
}

$rows = $db->fetchAll(
    $selectParts,
    Typecho_Db::buildWhere($selectParts, $whereParts),
    Typecho_Db::buildParams($selectParts, $whereParts),
    $db->fetchAll(array(
        'table'   => TypechoDb::getTable('contents'),
        'fields'  => array('cid'),
        'where'   => $whereParts['where'],
        'params'  => $whereParts['params'],
        'order'   => 'modified DESC',
        'limit'   => 2000,
    ))
);

/* ------------------------------------------------------------------ *
 * 输出
 * ------------------------------------------------------------------ */
header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex', true);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?php echo htmlspecialchars($sitemapSiteUrl, ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
<?php foreach ($rows as $row) { ?>
    <url>
        <loc><?php echo htmlspecialchars($sitemapSiteUrl . trim((string) $row['slug'], '/') . '/', ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></loc>
        <lastmod><?php echo date('c', $row['modified']); ?></lastmod>
        <changefreq><?php echo $row['type'] === 'page' ? 'monthly' : 'weekly'; ?></changefreq>
        <priority><?php echo $row['type'] === 'page' ? '0.6' : '0.8'; ?></priority>
    </url>
<?php } ?>
</urlset>