# VOID 主题 · 加固与现代化分支

> 基于 [`AlanDecode/Typecho-Theme-VOID`](https://github.com/AlanDecode/Typecho-Theme-VOID) 3.5.1
> 原仓库自 2023-04 起停更，本分支补齐安全加固、SEO 与构建链维护。

---

## 本分支做了什么

### P0 · 安全

| 问题 | 说明 | 修复 |
|---|---|---|
| **Stored XSS** | `includes/head.php` 把 `fields->banner`、`fields->excerpt` 直接拼进 `meta` 属性；项目内 `htmlspecialchars` 使用次数为 **0** | 新增 `Utils::esc()`，全部经 `ENT_QUOTES` + UTF-8 转义 |
| **属性注入** | 封面图 `src` / `data-src` 共 6 处裸输出 | 统一经 `Utils::esc()` |
| **JSON-LD 注入** | `includes/ldjson.php` 的 `description`、`includes/archives.php` 的 `excerpt` 裸输出 | 已转义 |
| **CSS 注入** | 后台 `brandFont` 的 `src` / `style` / `weight` 直接进 `<style>` | 限定 `http(s)://` 或 `/` 开头；`style`、`weight` 走白名单 |
| **JS 注入** | `VOIDConfig` 中 `darkModeTime`、`indexStyle`、`version` 未强制类型 | 全部 `(int)` 强转 |
| **Twitter 账号** | `twitterId` 裸输出，且后台留空时会输出空 `@` | `trim` 去 `@` + 转义，留空则不输出该标签 |

新增 `.htaccess`：安全响应头（`X-Frame-Options`、`CSP`、`nosniff`、`Referrer-Policy`、`Permissions-Policy`）、目录保护、隐藏文件拦截、静态缓存、压缩。
**Nginx 等价配置见下方「部署」一节。**

### P0 · SEO

| 项 | 变更 |
|---|---|
| `canonical` | **新增** `<link rel="canonical">`，避免多路径分散权重 |
| `og:locale` | 新增 |
| `og:image:alt` / `twitter:image:alt` | 新增 |
| `article:author` | 新增 |
| `og:type` | 由三元表达式改为显式分支，避免拼接歧义 |
| `sitemap.xml` | 新增 `sitemap.php`，支持 `?type=post\|page\|all`、`?days=N` |
| `robots.txt` | 新增 `robots.txt.example` 模板 |

### P1 · 稳定性与体验

- **消除暗色模式白屏闪烁** — 在 `<head>` 内加内联定色脚本，先于 CSS 执行；判定顺序为「强制亮色 → Cookie → `prefers-color-scheme`」。为此调整了 `header.php` / `head.php` 的加载顺序（`header.php` 需先执行以提供 `$inlineColorScheme`）。
- **社交卡片图回退** — 文章未设封面时回退到主题图标，`og:image` 不再为空。

### P2 · 构建链

`node-sass@6` 已弃用（含原生绑定，在新版 Node 上编译失败），已迁移：

| 原 | 现 |
|---|---|
| `node-sass ^6.0.1` | `sass ^1.77`（dart-sass，纯 JS） |
| `gulp ^4` | `gulp ^5` |
| `eslint ^7`（EOL） | `eslint ^8.57` |
| `gulp-autoprefixer ^8` | `^9` |
| `del ^6` | `^7` |

新增 npm 脚本：`build`、`watch`、`lint`、`lint:php`。

### P2 · CI

`.github/workflows/ci.yml` 四道关卡：

1. **PHP 8.2 语法检查** — 全量 `php -l`
2. **裸输出守卫** — 匹配 `echo $this->fields->banner|excerpt;` 即失败，防止回归
3. **安全头校验** — `.htaccess` 必须含四类核心头
4. **SEO 标签校验** — `canonical`、`og:locale`、`og:image:alt` 必须存在

另加前端构建任务。

---

## 部署

### Apache

主题根目录的 `.htaccess` 需被 Apache 读到。若 `AllowOverride None`，请在站点配置中手动添加：

```apache
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.bootcdn.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; img-src 'self' data: blob: https:; frame-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'"
</IfModule>
```

> CSP 默认已放开常见 CDN。若站点用了别的统计 / 评论服务，请按 `.htaccess` 内的注释逐项追加来源，**不要直接删整条策略**。

### Nginx

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: blob: https:; frame-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'" always;
location ~ ^/(admin|action)/ { deny all; }
location ~ /\.(?!well-known) { deny all; }
```

### robots.txt

把 `robots.txt.example` 复制到 **Typecho 根目录**（与 `index.php` 同级），并确认 `Sitemap:` 指向 `/usr/themes/VOID/sitemap.php`。

---

## 从源码构建

```bash
npm install
npm run build
```

`node-sass` 已移除，**无需 Python 或编译工具链**。

---

## 兼容性

| 项 | 状态 |
|---|---|
| PHP 8.3 | ✅ 全部 19 个文件 `php -l` 通过 |
| PHP 8.2 | ✅ CI 校验 |
| Typecho 1.2.0 | ✅ 沿用原主题适配 |
| PHP 8 弃用函数 | ✅ 无 `each` / `create_function` / `mysql_*` |

---

## 已知未处理

| 项 | 原因 |
|---|---|
| 第三方 CDN 资源未加 SRI | `fonts.googleapis.com` 等动态返回内容，加 SRI 需固定哈希，维护成本高 |
| 未做 `hreflang` | 主题无法感知站点是否启用多语言，留给站点按需在 `主题设置 → head` 里自行添加 |
| 未提供 `noscript` 图片回退 | 懒加载脚本已处理，纯 `noscript` 回退会重复加载图片 |

---

## 致谢

原作者 [熊猫小A / AlanDecode](https://github.com/AlanDecode/Typecho-Theme-VOID)，MIT 许可。
本分支仅做加固与维护性改进，主题设计归原作者所有。