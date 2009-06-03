<?php
// $Id: node.tpl.php,v 1.1 2008/02/09 10:50:41 vadbarsdrupalorg Exp $
?>
<div id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?>">


<?php if ($page == 0): ?>
  <h2><a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
<?php endif; ?>

  <div class="clear-block">
  <?php if ($picture) print $picture; ?>
  <?php if ($submitted): ?>
    <span class="submitted_author"><?php print theme('username', $node)?></span>
    <span class="submitted"><?php print t('Posted at ') . format_date($node->created, 'custom', "H:i o\\n D, m/d/Y"); ?></span>
  <?php endif; ?>
  <?php if ($taxonomy): ?>
    <div class="terms"><?php print t('<b>Tags:</b> ').$terms ?></div>
  <?php endif;?>
  </div>

  <div class="content">
    <?php print $content ?>
  </div>

  <div class="clear-block">
    <?php if ($links): ?>
      <div class="links"><?php print $links; ?></div>
    <?php endif; ?>
  </div>

</div>
