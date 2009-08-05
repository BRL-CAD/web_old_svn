<?php
// $Id$
?>
<div id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?>">

<?php if ($page == 0): ?>
  <h2><a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
<?php endif; ?>

  <div class="clear-block">
  <div style="float: right">
  <?php if (!empty($node->content['fivestar_widget']['#value'])) print $node->content['fivestar_widget']['#value']; ?>
  <?php if ($picture) print $picture; ?>
  </div>
  <div style="float: left">
  <?php if ($submitted) { ?><span class="submitted"><?php print $submitted; ?></span><?php }?>
  <div class="terms"><?php echo t('License:'), phptemplate_vocabulary_links($node, 2); ?><br />
  <?php echo t('Tags:'), phptemplate_vocabulary_links($node, 1); ?></div>
  </div>
  </div>

  <div class="content">
    <?php if(!empty($node->content['field_image_file']['#value'])) print $node->content['field_image_file']['#value']?>
    <?php print $node->content['field_license']['#value'] ?>
    <?php if(!$teaser && !empty($node->content['group_files']['#children'])) print $node->content['group_files']['#children'] ?>
    <?php if(!empty($node->content['group_metadata']['#children'])) print $node->content['group_metadata']['#children'] ?>
    <?php print $node->content['body']['#value'] ?>
  </div>

  <div class="clear-block">
    <?php if ($links): ?>
      <div class="links"><?php print $links; ?></div>
    <?php endif; ?>
  </div>

</div>
