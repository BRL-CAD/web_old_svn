<?php // $Id$ ?>
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

  <?php if ($page) {?>
  <div class="links clear-block">
    <?php if (!empty($node->content['service_links']['#value'])) echo $node->content['service_links']['#value']; ?>
	<div class="service-links"><div class="service-label"><?php echo t('Link to this page'), ':'?></div>
	<input class="link-code" onfocus="this.select()" title="<?php echo t('Click to select the text.')?>" type="text" readonly="readonly" value="&lt;a href=&quot;<?php echo url('node/' . $node->nid, null, null, true) ?>&quot;&gt;<?php echo check_plain($node->title)?>&lt;/a&gt;" />
	</div>
  </div>
  <?php }?>

  <div class="links clear-block">
    <?php if (!empty($links)) echo $links; ?>
  </div>
</div>
