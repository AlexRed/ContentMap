<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<meta name="gmapkey" content="<?php echo $params->get('apikey'); ?>" />
<script src="<?php if ($params->get('menu')== 0):?><?php echo JURI::root(true).'/modules/mod_contentmap/gmapez-2.5.js';?><?php endif; ?><?php if ($params->get('menu')== 1):?><?php echo JURI::root(true).'/modules/mod_contentmap/gmapez-2.5_1.js';?><?php endif; ?><?php if ($params->get('menu')== 2):?><?php echo JURI::root(true).'/modules/mod_contentmap/gmapez-2.5_2.js';?><?php endif; ?>"
      type="text/javascript"></script>
<div class="GMapEZ <?php echo $params->get('maptype'); ?> GLargeMapControl GMapTypeControl <?php if ($params->get('overviewmap')== 1) :?>GOverviewMapControl<?php endif; ?>" style="width: <?php echo $params->get('mapwidth'); ?>px; height: <?php echo $params->get('mapheight'); ?>px;">
<?php foreach ($list as $item) : ?>
  <a href="http://maps.google.com/maps?ll=<?php echo $item->keyref; ?>&amp;spn=1.0,1.0&amp;t=k&amp;hl=it"><?php echo $params->get('marker'); ?></a>
  <div>
    <a href="<?php echo $item->link; ?>"><?php echo $item->text; ?></a>
	<?php if ($params->get('data')== 1) :?> 
	<br /><?php echo $item->data; ?>
	<?php endif; ?>
	<?php if ($params->get('indirizzo')== 1) :?> 
	<br /><?php echo $item->indirizzo; ?>
	<?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
<div id="contentmap" style="width:<?php echo $params->get('mapwidth'); ?>px;" ><?php require("./modules/mod_contentmap/tmpl/contentmap.css"); ?></div><br />

