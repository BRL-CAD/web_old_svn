<?php
// $Id: comment.tpl.php,v 1.1 2008/02/09 10:50:41 vadbarsdrupalorg Exp $
?>
<div class="comment<?php print ($comment->new) ? ' comment-new' : ''; print (isset($comment->status) && $comment->status  == COMMENT_NOT_PUBLISHED) ? ' comment-unpublished' : ''; print ' '. $zebra; ?>">

  <div class="clear-block">

  <?php if ($comment->new) : ?>
    <span class="new"><?php print drupal_ucfirst($new) ?></span>
  <?php endif; ?>
  <h3><?php print $title ?></h3>

  <?php if ($picture) print $picture; ?>
  <?php if ($submitted): ?>
    <span class="submitted_author"><?php print t('!username', array('!username' => theme('username', $comment))); ?></span>
    <span class="submitted"><?php print t('Posted at ') . format_date($comment->timestamp, 'custom', "H:i o\\n D, m/d/Y"); ?></span>
  <?php endif; ?>

    <div class="content">
      <?php print $content ?>
      <?php if ($signature): ?>
      <div class="clear-block">
        <div>—</div>
        <?php print $signature ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($links): ?>
    <div class="links"><?php print $links ?></div>
  <?php endif; ?>
</div>
