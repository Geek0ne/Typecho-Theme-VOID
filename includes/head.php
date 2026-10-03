<?php
/**
 * head.php
 * 
 * <head>
 * 
 * @author      熊猫小A
 * @version     2019-01-15 0.1
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$setting = $GLOBALS['VOIDSetting']; 

if (isset($_POST['void_action'])) {
    if ($_POST['void_action'] == 'getLoginAction') {
        echo $this->options->loginAction;
        exit;
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="HandheldFriendly" content="true">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php if (!empty($inlineColorScheme)): ?>
    <script><?php echo $inlineColorScheme; ?></script>
    <?php endif; ?>
    <?php 
    $banner = '';
    $description = '';
    if($this->is('post') || $this->is('page')){
        if(isset($this->fields->banner) && $this->fields->banner != '')
            $banner = Utils::esc($this->fields->banner);
        if(isset($this->fields->excerpt) && $this->fields->excerpt != '')
            $description = Utils::esc($this->fields->excerpt);
    }else{
        $description = Utils::esc(Helper::options()->description);
    }

    // 社交卡片图片：文章未设 banner 时回退到站点图标
    $socialImage = $banner;
    if ($socialImage === '' && isset($this->options->themeUrl)) {
        $socialImage = Utils::esc($this->options->themeUrl('/assets/favicon.ico', true));
    }

    // Twitter 账号：后台可留空，留空则不输出相关标签
    $twitterId = isset($setting['twitterId']) ? trim((string) $setting['twitterId'], " \t\n\r\0\x0B@") : '';

    // 当前页面 URL（canonical / og:url 共用）
    $currentUrl = $this->permalink;
    ?>
    <title><?php Contents::title($this); ?></title>
    <meta name="author" content="<?php $this->author(); ?>" />
    <meta name="description" content="<?php if($description != '') echo $description; else Utils::esc($this->excerpt(50)); ?>" />
    <link rel="canonical" href="<?php echo $currentUrl; ?>" />
    <?php if ($this->is('post') || $this->is('page')): ?>
    <meta property="og:title" content="<?php Contents::title($this); ?>" />
    <meta property="og:description" content="<?php if($description != '') echo $description; else Utils::esc($this->excerpt(50)); ?>" />
    <meta property="og:site_name" content="<?php Contents::title($this); ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="<?php echo $currentUrl; ?>" />
    <?php if ($socialImage !== ''): ?>
    <meta property="og:image" content="<?php echo $socialImage; ?>" />
    <meta property="og:image:alt" content="<?php Contents::title($this); ?>" />
    <?php endif; ?>
    <meta property="og:locale" content="<?php echo str_replace('-', '_', (string) $this->options->lang); ?>" />
    <meta property="article:published_time" content="<?php echo date('c', $this->created); ?>" />
    <meta property="article:modified_time" content="<?php echo date('c', $this->modified); ?>" />
    <meta property="article:author" content="<?php $this->author(); ?>" />
    <?php else: ?>
    <meta property="og:title" content="<?php Contents::title($this); ?>" />
    <meta property="og:description" content="<?php if($description != '') echo $description; else Utils::esc(Helper::options()->description); ?>" />
    <meta property="og:site_name" content="<?php $this->options->title(); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo $currentUrl; ?>" />
    <?php if ($socialImage !== ''): ?>
    <meta property="og:image" content="<?php echo $socialImage; ?>" />
    <meta property="og:image:alt" content="<?php $this->options->title(); ?>" />
    <?php endif; ?>
    <meta property="og:locale" content="<?php echo str_replace('-', '_', (string) $this->options->lang); ?>" />
    <?php endif; ?>
    <meta name="twitter:title" content="<?php Contents::title($this); ?>" />
    <meta name="twitter:description" content="<?php if($description != '') echo $description; else Utils::esc($this->excerpt(50)); ?>" />
    <meta name="twitter:card" content="summary" />
    <?php if ($twitterId !== ''): ?>
    <meta name="twitter:site" content="@<?php echo Utils::esc($twitterId); ?>" />
    <meta name="twitter:creator" content="@<?php echo Utils::esc($twitterId); ?>" />
    <?php endif; ?>
    <?php if ($socialImage !== ''): ?>
    <meta name="twitter:image" content="<?php echo $socialImage; ?>" />
    <meta name="twitter:image:alt" content="<?php Contents::title($this); ?>" />
    <?php endif; ?>
    <?php $this->header('commentReply=&description=&'); ?>

    <!--CSS-->
    <link rel="stylesheet" href="<?php Utils::indexTheme('/assets/bundle.css');?>">
    <link rel="stylesheet" href="<?php Utils::indexTheme('/assets/VOID.css');?>">

    <!--JS-->
    <script src="<?php Utils::indexTheme('/assets/bundle-header.js'); ?>"></script>
    <script>
    VOIDConfig = {
        PJAX : <?php echo $setting['pjax'] ? 'true' : 'false'; ?>,
        searchBase : "<?php Utils::index("/search/"); ?>",
        home: "<?php Utils::index("/"); ?>",
        buildTime : "<?php Utils::getBuildTime(); ?>",
        enableMath : <?php echo $setting['enableMath'] ? 'true' : 'false'; ?>,
        lazyload : <?php echo $setting['lazyload'] ? 'true' : 'false'; ?>,
        colorScheme:  <?php echo $setting['colorScheme']; ?>,
        headerMode: <?php echo $setting['headerMode']; ?>,
        followSystemColorScheme: <?php echo $setting['followSystemColorScheme'] ? 'true' : 'false'; ?>,
        browserLevelLoadingLazy: <?php echo $setting['browserLevelLoadingLazy'] ? 'true' : 'false'; ?>,
        VOIDPlugin: <?php echo $setting['VOIDPlugin'] ? 'true' : 'false'; ?>,
        votePath: "<?php Utils::index('/action/void?'); ?>",
        lightBg: "",
        darkBg: "",
        lineNumbers: <?php echo $setting['lineNumbers'] ? 'true' : 'false'; ?>,
        darkModeTime: {
            'start': <?php echo (int) $setting['darkModeTime']['start']; ?>,
            'end': <?php echo (int) $setting['darkModeTime']['end']; ?>
        },
        horizontalBg: <?php echo empty($setting['siteBg']) ? 'false' : 'true'; ?>,
        verticalBg: <?php echo empty($setting['siteBgVertical']) ? 'false' : 'true'; ?>,
        indexStyle: <?php echo (int) $setting['indexStyle']; ?>,
        version: <?php echo (int) $GLOBALS['VOIDVersion']; ?>,
        isDev: true
    }
    </script>
    <script src="<?php Utils::indexTheme('/assets/header.js'); ?>"></script>
    
    <?php echo $setting['head']; ?>
    <style>
        <?php if(!empty($setting['desktopBannerHeight'])): ?>
        @media screen and (min-width: 768px){
            main>.lazy-wrap{min-height: <?php echo $setting['desktopBannerHeight']; ?>vh;}
        }
        <?php endif; ?>

        <?php if(!empty($setting['mobileBannerHeight'])): ?>
        @media screen and (max-width: 768px){
            main>.lazy-wrap{min-height: <?php echo $setting['mobileBannerHeight']; ?>vh;}
        }
        <?php endif; ?>
    </style>

    <?php if (array_key_exists('src', $setting['brandFont']) && !empty($setting['brandFont']['src'])): ?>
    <?php
        // 字体地址可能来自后台设置，限定为 http(s)/相对路径，
        // 挡掉 url(javascript:...) 之类的样式注入。
        $brandFontUrl = trim((string) $setting['brandFont']['src']);
        $brandFontSafe = preg_match('#^(https?:)?//#i', $brandFontUrl) || strpos($brandFontUrl, '/') === 0;
        // font-style / font-weight 只允许有限取值，避免闭合样式块
        $brandStyle = in_array($setting['brandFont']['style'], array('normal','italic','oblique'), true)
            ? $setting['brandFont']['style'] : 'normal';
        $brandWeight = in_array((string) $setting['brandFont']['weight'], array('100','200','300','400','500','600','700','800','900','bold','normal'), true)
            ? $setting['brandFont']['weight'] : 'normal';
    ?>
    <?php if ($brandFontSafe): ?>
    <style>
    @font-face {
        font-family: "BrandFont";
        src: url("<?php echo Utils::esc($brandFontUrl); ?>");
    }
    .brand {
        font-family: BrandFont, sans-serif;
        font-style: <?php echo $brandStyle; ?>!important;
        font-weight: <?php echo $brandWeight; ?>!important;
    }
    </style>
    <?php endif; ?>
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700&display=swap" rel="stylesheet">
    <?php if(Utils::isSerif($setting)): ?>
        <link id="stylesheet_noto" href="https://fonts.googleapis.com/css?family=Noto+Serif+SC:300,400,700&display=swap&subset=chinese-simplified" rel="stylesheet">
    <?php endif; ?>

    <?php if($setting['useFiraCodeFont']): ?>
        <link href="https://fonts.googleapis.com/css?family=Fira+Code&display=swap" rel="stylesheet">
        <style>.yue code, .yue tt {font-family: "Fira Code", Menlo, Monaco, Consolas, "Courier New", monospace}</style>
    <?php endif; ?>

    </head>
