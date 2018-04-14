<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Crypsis
 * @subpackage      BBCode
 *
 * @copyright       Copyright (C) 2008 - 2018 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die();

$title    = $this->title;
$url      = $this->url;
$filename = $this->filename;
$size     = $this->size;
$alt      = $this->alt;
// @var  bool $canLink False if image is inside a link: [url=http://www.domain.com][img]image.jpg[/img][/url]

$canLink = isset($this->canLink) ? $this->canLink : true;

echo $this->subLayout('Widget/Lightbox');

$config = KunenaConfig::getInstance();

$attributesLink = $config->lightbox ? ' class="fancybox-button" rel="fancybox-button"' : '';
$width          = $size ? (int) $size . "px;" : 'auto ';
$attributesImg  = ' style="max-height: ' . (int) $config->imageheight . 'px;' . ' max-width:' . $width . '"';
$attributesImg  .= $alt ? ' alt="' . htmlspecialchars($alt) . '"' : '';

if ($config->lazyload)
{
	?>
	<?php if ($canLink) : ?>
		<a href="<?php echo $this->escape($url); ?>" title="<?php echo $alt;?>" <?php echo $attributesLink; ?>>
	<?php endif; ?>

	<img class="lazy" src="<?php echo $this->escape($url); ?>"
	     data-src="<?php echo $this->escape($url); ?>"<?php echo $attributesImg; ?> width="<?php echo $width; ?>"
	     alt="<?php echo $title;?>"/>

	<?php if ($canLink) : ?>
		</a>
	<?php endif; ?>

	<?php
}
else
{
	?>
	<div class="kmsgimage">
		<?php if ($canLink) : ?>
		<a href="<?php echo $this->escape($url); ?>" title="<?php echo $alt;?>" <?php echo $attributesLink; ?>>
			<?php endif; ?>

			<img src="<?php echo $this->escape($url); ?>" <?php echo $attributesImg; ?>  alt="<?php echo $alt;?>"/>

			<?php if ($canLink) : ?>
		</a>
	<?php endif; ?>
	</div>
<?php } ?>
