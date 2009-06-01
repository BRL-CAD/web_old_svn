<?php
if( !isset( $GLOBALS[ 'int' ] ) ) $GLOBALS[ 'int' ] = 0;
if( $block->subject!='HOMEPAGEBLOCK' ) $GLOBALS[ 'int' ]++;
?>
			<div class="block block-<?php print $block->module . ' blockid-' . $GLOBALS[ 'int' ]; ?>" id="block-<?php print $block->module; ?>-<?php print $block->delta; ?>">
				<div class="rb1"></div><div class="rb2"></div><div class="rb3"></div><div class="rb4"></div><div class="rb5"></div><div class="rb6"></div><div class="rb7"></div>
				<div class="rboxcontent">
					<?php if( $block->subject=='HOMEPAGEBLOCK' ) { ?><h2 class="title"><?php print $block->subject; ?></h2><?php } ?>
					<?php print $block->content; ?>
				</div>
				<div class="rb7"></div><div class="rb6"></div><div class="rb5"></div><div class="rb4"></div><div class="rb3"></div><div class="rb2"></div><div class="rb1"></div>
			</div>
