<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <?php if (isset($js_script)) : ?>
            <?php foreach ($js_script as $script) : ?>
                <script type="text/javascript" src="<?= @safeCher($key, '') ?>"></script>
            <?php endforeach; ?>
        <?php endif; ?>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>ajaxurl = "<?= buildFullUrlWithPath('ajax');  ?>"</script>
        <title><?= htmlspecialchars(@safeCher($title ?? null, '節子と成長教室')) ?></title>
        <link href="<?= getCss("style") ?>" rel="stylesheet" type="text/css"/>
        <link href="<?= getCss("top") ?>" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <header>
            <div class="header-logo-area">
                <img src="<?= getImgPage('logo.png'); ?>" />
            </div>
            <div class="header-link-area">
                <a href="">授業</a>
                <a href="">成長日記</a>
                <a href="">職員室</a>
                <a href="">図書室</a>
            </div>
            <div class="header-user-area">
            <img src="" />
            </div>
        </header>
        <div class="illustration-school">