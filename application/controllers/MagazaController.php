<?php
/**
 * The Header template for our theme *
 * Displays all of the <head> section and everything up till <div id="main"> *
 * @package WordPress
 * @subpackage Bau_Mezun
 * @since Bau Mezun 1.0
 */
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="alternate" type="application/rss+xml" title="B.A.R.C Beslemesi"
          href="http://mezun.bahcesehir.edu.tr/feed/"/>
    <link rel="stylesheet" href=" <?php echo get_template_directory_uri(); ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href=" <?php echo get_template_directory_uri(); ?>/style.css">
    <link rel="stylesheet" href=" <?php echo get_template_directory_uri(); ?>/barc.css">
    <link rel="shortcut icon" href=" <?php echo get_template_directory_uri(); ?>/img/favicon.ico"/>
    <script type="text/javascript" src="//use.typekit.net/yxl6rke.js"></script>
    <script type="text/javascript">try {
            Typekit.load();
        } catch (e) {
        }</script>
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-53aab0d64b24b985"></script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]--></head>
<body>
<header>
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12 col-xs-12 logo">
                <h1><a href="<?php bloginfo('url'); ?>">B.A.R.C - BAU Alumni Relations Center</a></h1>
            </div>
            <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12 head-info">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 social">
                    <span>Sosyal Ağlar'da <b>BARC</b></span>

                    <a href="http://instagram.com/baubarc" class="in" target="_blank"></a>
                    <a href="https://twitter.com/BAlumnioffice" class="tw" target="_blank"></a>
                    <a href="https://www.facebook.com/baualumnirelations?fref=ts" class="fb" target="_blank"></a>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 login no-padding-right">
                    <span><b>BARC</b>'a Bağlanın</span>
                    <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#logind">Mezun Bilgi
                        Sistemi'ne Giriş
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
<section class="menu-wrap">
    <div class="container">
        <div class="navbar" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            <?php
            $menu_name = 'primary';
            if (($locations = get_nav_menu_locations()) && isset($locations[$menu_name])) {
                $menu = wp_get_nav_menu_object($locations[$menu_name]);
                $menu_items = wp_get_nav_menu_items($menu->term_id);
                $menu_list .= '';
                foreach ((array)$menu_items as $key => $menu_item) {
                    if ($menu_item->menu_item_parent == 0) {
                        $menu_list .= '<li class="dropdown">';
                        $title = $menu_item->title;
                        $url = $menu_item->url;
                        $mip = $menu_item->ID;
                        $menu_list .= '
									<a href="' . $url . '" data-toggle="dropdown">' . $title . '<b class="caret"></b></a>';
                        $menu_list .= '
									<ul class="dropdown-menu">';
                        $altmenu = alt_menu_al($mip, $menu_name);
                        foreach ((array)$altmenu as $key => $menu) {
                            $title = $menu[title];
                            $url = $menu[url];
                            $menu_list .= '
													<li><a href="' . $url . '">' . $title . '</a></li>';

                        }
                        $menu_list .= '</ul>';
                        $menu_list .= '</li>';
                    }

                }
            }
            // $menu_list now ready to output
            ?>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <?php echo $menu_list ?>
                    <li><a href="<?php bloginfo('url'); ?>/iletisim">İletişim</a></li>
                </ul>
            </div>
        </div>
    </div>

</section>