<?php
/**
 * Kunena Component
 * @package Kunena.Administrator.Template
 * @subpackage Attachments
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

$document = JFactory::getDocument();
$document->addStyleSheet ( JUri::base(true).'/components/com_kunena/media/css/admin.css' );
if (JFactory::getLanguage()->isRTL()) $document->addStyleSheet ( JUri::base(true).'/components/com_kunena/media/css/admin.rtl.css' );
?>
<div id="kadmin">
	<div class="kadmin-left"><?php include KPATH_ADMIN.'/template/joomla25/common/menu.php'; ?></div>
	<div class="kadmin-right">
	<div class="kadmin-functitle icon-files"><?php echo JText::_('COM_KUNENA_ATTACHMENTS_VIEW'); ?></div>
		<form action="<?php echo KunenaRoute::_('administrator/index.php?option=com_kunena') ?>" method="post" id="adminForm" name="adminForm">
			<input type="hidden" name="view" value="attachments" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering') ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape ($this->state->get('list.direction')) ?>" />
			<input type="hidden" name="limitstart" value="<?php echo intval ( $this->pagination->limitstart ) ?>" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_( 'form.token' ); ?>

			<fieldset id="filter-bar">
				<div class="filter-search fltlft">
					<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_KUNENA_FILTER'); ?>:</label>
					<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('list.search')); ?>" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />

					<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>
				<div class="filter-select fltrt">
					<select name="filter_order_Dir" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
						<?php echo JHtml::_('select.options', $this->sortDirectionOrdering, 'value', 'text', $this->escape ($this->state->get('list.direction')));?>
					</select>
				</div>
				</fieldset>
			<div class="clr"> </div>

			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th align="center" width="5">#</th>
						<th width="5"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count ( $this->items ); ?>);" /></th>
						<th class="title"><?php echo JHtml::_('grid.sort', 'COM_KUNENA_FILENAME', 'a.filename', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
						<th class="center"><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_FILETYPE', 'a.filetype', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
						<th class="center"><?php echo JHtml::_('grid.sort', 'COM_KUNENA_FILESIZE', 'a.size', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?>
						<th class="center"><?php echo JText::_('COM_KUNENA_ATTACHMENTS_FIELD_LABEL_IMAGEDIMENSIONS'); ?>	</th>
						<th class="center"><?php echo JText::_('COM_KUNENA_ATTACHMENTS_USERNAME'); ?></th>
						<th class="center"><?php echo JText::_('COM_KUNENA_MESSAGE'); ?></th>
						<th class="center"><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
					</tr>
					<tr>
						<td class="center">
						</td>
						<td class="center">
						</td>
						<td class="nowrap">
							<label for="filter_title" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
							<input class="input-block-level input-filter filter" type="text" name="filter_title" id="filter_title" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" value="<?php echo $this->filterTitle; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" />
						</td>
						<td class="nowrap">
							<label for="filter_type" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
							<input class="input-block-level input-filter filter" type="text" name="filter_type" id="filter_type" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" value="<?php echo $this->filterType; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" />
						</td>
						<td class="nowrap">
							<label for="filter_size" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
							<input class="input-block-level input-filter filter" type="text" name="filter_size" id="filter_size" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" value="<?php echo $this->filterSize; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" />
						</td>
						<td class="nowrap">
						<?php /*
							<label for="filter_dims" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
							<input class="input-block-level input-filter filter" type="text" name="filter_dims" id="filter_dims" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" value="<?php echo $this->filterDimensions; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" />
						*/ ?>
						</td>
						<td class="nowrap">
							<label for="filter_username" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
							<input class="input-block-level input-filter filter" type="text" name="filter_username" id="filter_username" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" value="<?php echo $this->filterUsername; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" />
						</td>
						<td class="nowrap">
							<label for="filter_post" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
							<input class="input-block-level input-filter filter" type="text" name="filter_post" id="filter_post" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" value="<?php echo $this->filterPost; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL') ?>" />
						</td>
						<td class="nowrap center hidden-phone">
						</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="14">
							<div class="pagination">
								<div class="limit"><?php echo JText::_('COM_KUNENA_A_DISPLAY'); ?> <?php echo $this->pagination->getLimitBox (); ?></div>
								<?php echo $this->pagination->getPagesLinks (); ?>
								<div class="limit"><?php echo $this->pagination->getResultsCounter (); ?></div>
							</div>
						</td>
					</tr>
				</tfoot>
		<?php
			$k = 0;
			$i = 0;
			$n = count($this->items);
			foreach($this->items as $id=>$attachment) {
				$instance = KunenaForumMessageAttachmentHelper::get($attachment->id);
				$message = $instance->getMessage();
				$path = JPATH_ROOT.'/'.$attachment->folder.'/'.$attachment->filename;
				if ( $instance->isImage($attachment->filetype) && is_file($path)) list($width, $height) =	getimagesize( $path );
		?>
			<tr <?php echo 'class = "row' . $k . '"';?>>
				<td class="right"><?php echo $i + $this->pagination->limitstart + 1; ?></td>
				<td><?php echo JHtml::_('grid.id', $i, intval($attachment->id)) ?></td>
				<td class="left" width="70%"><?php echo $instance->getThumbnailLink() . ' ' . KunenaForumMessageAttachmentHelper::shortenFileName($attachment->filename, 10, 15) ?></td>
				<td class="center"><?php echo $this->escape($attachment->filetype); ?></td>
				<td class="center"><?php echo number_format ( intval ( $attachment->size ) / 1024, 0, '', ',' ) . ' KB'; ?></td>
				<td class="center"><?php echo isset($width) && isset($height) ? $width . ' x ' . $height  : '' ?></td>
				<td class="center"><?php echo $this->escape($message->name); ?></td>
				<td class="center"><?php echo $this->escape($message->subject); ?></td>
				<td class="center"><?php echo intval($attachment->id); ?></td>
			</tr>
				<?php
				$i++;
				$k = 1 - $k;
				}
				?>
		</table>

		</form>
	</div>
	<div class="kadmin-footer">
		<?php echo KunenaVersion::getLongVersionHTML (); ?>
	</div>
</div>

