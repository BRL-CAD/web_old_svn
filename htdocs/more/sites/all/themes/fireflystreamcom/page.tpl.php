<?php
// $Id: page.tpl.php,v 1.2 2008/02/15 07:24:58 vadbarsdrupalorg Exp $
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language ?>" lang="<?php print $language ?>">
  <head>
    <title><?php print $head_title ?></title>
    <?php print $head ?>
    <?php print $styles ?>
    <?php print $scripts ?>
    <!--[if lt IE 7]>
      <?php print phptemplate_get_ie_styles(); ?>
    <![endif]-->

    <link rel="Shortcut Icon" type="image/x-icon"
    href="<?php print base_path() . path_to_theme() ?>/favicon.ico" />

  </head>
  <body<?php print phptemplate_body_class($sidebar_left, $sidebar_right); ?>>
<!-- Layout -->
    <div id="wrapper">
    <div id="container" class="clear-block">
      <div id="header">
        <div id="logo-floater">
        <?php
          // Prepare header
          $site_fields = array();
          if ($site_name) {
            $site_fields[] = check_plain($site_name);
          }
          if ($site_slogan) {
            $site_fields[] = check_plain($site_slogan);
          }
          $site_title = implode(' ', $site_fields);
          if ($site_fields) {                                
            $site_fields[0] = '<span>'. $site_fields[0] .'</span>';
            $site_fields[1] = '<div class="slogan">'. $site_fields[1] .'</div>';
          }
          $site_html = implode(' ', $site_fields);

          if ($logo || $site_title) {
            print '<h1><a href="'. check_url($base_path) .'" title="'. $site_title .'">';
            if ($logo) {
              print '<img src="'. check_url($logo) .'" alt="'. $site_title .'" id="logo" />';
            }
            print $site_html.'</a></h1>';
          }

        ?>
        </div>
      <?php if ($search_box): ?><div class="searchblock"><?php print $search_box ?></div><?php endif; ?>
      <?php if (!$search_box): ?><div class="nosearchblock">&nbsp;</div><?php endif; ?>
      <div id="toplinks">
        <?php if (isset($primary_links)) : ?>
          <?php print theme('links', $primary_links, array('class' => 'links primary-links')) ?>
        <?php endif; ?>
        <?php if (isset($secondary_links)) : ?>
          <?php print theme('links', $secondary_links, array('class' => 'links secondary-links')) ?>
        <?php endif; ?>
      </div>
      </div> <!-- /header -->
    <div id="header-region" class="clear-block"><?php print $header; ?></div>

      <?php if ($sidebar_left): ?>
        <div id="sidebar-left" class="sidebar">
          <?php print $sidebar_left ?>
        </div>
      <?php endif; ?>

      <div id="center"><div id="squeeze"><div class="right-corner"><div class="left-corner">
          <?php if(!$is_front && $breadcrumb): print $breadcrumb; endif; ?>
          <?php if ($mission): print '<div id="mission">'. $mission .'</div>'; endif; ?>
          <?php if ($tabs): print '<div id="tabs-wrapper" class="clear-block">'; endif; ?>
          <?php if ($title): print '<h2'. ($tabs ? ' class="with-tabs"' : '') .'>'. $title .'</h2>'; endif; ?>
          <?php if ($tabs): print '<ul class="tabs primary">'. $tabs .'</ul></div>'; endif; ?>
          <?php if ($tabs2): print '<ul class="tabs secondary">'. $tabs2 .'</ul>'; endif; ?>
          <?php print $help; ?>
          <?php if ($messages): print $messages; endif; ?>
          <div class="clear-block">
            <?php print $content ?>
            <?php print $feed_icons ?>
          </div>
      </div></div></div></div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->

      <?php if ($sidebar_right): ?>
        <div id="sidebar-right" class="sidebar">
          <?php print $sidebar_right ?>
        </div>
      <?php endif; ?>
    </div> <!-- /container -->
    <!-- Please don't remove this string -->
    <div id="footer-copyright">&copy; 2007 Designed by <a href="http://fireflystream.com" title="FireflyStream.com">FireflyStream.com</a></div>
    <!-- Please don't remove this string -->
    <div id="footer-region"><?php print $footer_message . $footer ?></div>
    <?php if ($search_box): ?><div class="searchblock_bottom"><?php print $search_box ?></div><?php endif; ?>
  </div>

<!-- /layout -->

  <?php print $closure ?>
  </body>
</html>
