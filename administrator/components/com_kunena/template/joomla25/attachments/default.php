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
?>

<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $this->state->get('list.ordering'); ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<div id="kunena" class="admin override">
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div id="j-sidebar-container" class="span2">
					<div id="sidebar">
						<div class="sidebar-nav"><?php include KPATH_ADMIN.'/template/joomla25/common/menu.php'; ?></div>
					</div>
				</div>
				<div id="j-main-container" class="span10">
					<form action="<?php echo KunenaRoute::_('administrator/index.php?option=com_kunena') ?>" method="post" id="adminForm" name="adminForm">
						<input type="hidden" name="view" value="attachments" />
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering') ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape ($this->state->get('list.direction')) ?>" />
						<input type="hidden" name="limitstart" value="<?php echo intval ( $this->pagination->limitstart ) ?>" />
						<input type="hidden" name="boxchecked" value="0" />
						<?php echo JHtml::_( 'form.token' ); ?>

						<div id="filter-bar" class="btn-toolbar">
							<div class="filter-search btn-group pull-left">
								<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCHIN');?></label>
								<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_KUNENA_ATTACHMENTS_FIELD_INPUT_SEARCHFILE'); ?>" value="<?php echo $this->escape($this->state->get('list.search')); ?>" title="<?php echo JText::_('COM_KUNENA_ATTACHMENTS_FIELD_INPUT_SEARCHFILE'); ?>" />
							</div>
							<div class="btn-group pull-left">
								<button class="btn tip" type="button" ><?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT'); ?></button>
								<button class="btn tip" type="button"  onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERRESET'); ?></button>
							</div>
							<div class="btn-group pull-right hidden-phone">
								<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
								<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
									<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
									<?php echo JHtml::_('select.options', $this->sortDirectionOrdering, 'value', 'text', $this->escape($this->state->get('list.direction')));?>
								</select>
							</div>
							<div class="clearfix"></div>
						</div>

						<table class="adminlist table table-striped">
							<thead>
								<tr>
									<th width="1%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count ( $this->items ); ?>);" /></th>
									<th><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_FIELD_LABEL_TITLE', 'a.filename', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
									<th><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_FIELD_LABEL_TYPE', 'a.filetype', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
									<th><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_FIELD_LABEL_SIZE', 'a.size', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?>
									<th><?php echo JText::_('COM_KUNENA_ATTACHMENTS_FIELD_LABEL_IMAGEDIMENSIONS'); ?>	</th>
									<th><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_USERNAME', 'username', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
									<th><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_FIELD_LABEL_MESSAGE', 'post', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
									<th class="center"><?php echo JHtml::_('grid.sort', 'COM_KUNENA_ATTACHMENTS_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering') ); ?></th>
								</tr>
								<tr>
									<td class="center">
									</td>
									<td class="nowrap">
										<label for="filter_title" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
										<input class="input-block-level input-filter filter" type="text" name="filter_title" id="filter_title" placeholder="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" value="<?php echo $this->filterTitle; ?>" title="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" />
									</td>
									<td class="nowrap">
										<label for="filter_type" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
										<input class="input-block-level input-filter filter" type="text" name="filter_type" id="filter_type" placeholder="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" value="<?php echo $this->filterType; ?>" title="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" />
									</td>
									<td class="nowrap">
										<label for="filter_size" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
										<input class="input-block-level input-filter filter" type="text" name="filter_size" id="filter_size" placeholder="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" value="<?php echo $this->filterSize; ?>" title="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" />
									</td>
									<td class="nowrap">
									<?php /*
										<label for="filter_dims" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
										<input class="input-block-level input-filter filter" type="text" name="filter_dims" id="filter_dims" placeholder="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" value="<?php echo $this->filterDimensions; ?>" title="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" />
									*/ ?>
									</td>
									<td class="nowrap">
										<label for="filter_username" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
										<input class="input-block-level input-filter filter" type="text" name="filter_username" id="filter_username" placeholder="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" value="<?php echo $this->filterUsername; ?>" title="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" />
									</td>
									<td class="nowrap">
										<label for="filter_post" class="element-invisible"><?php echo JText::_('COM_KUNENA_FIELD_LABEL_SEARCH_IN');?>:</label>
										<input class="input-block-level input-filter filter" type="text" name="filter_post" id="filter_post" placeholder="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" value="<?php echo $this->filterPost; ?>" title="<?php echo JText::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>" />
									</td>
									<td class="nowrap center hidden-phone">
									</td>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="8">
										<div class="pagination">
											<div class="limit"><?php echo JText::_('COM_KUNENA_A_DISPLAY'); ?> <?php echo $this->pagination->getLimitBox (); ?></div>
											<?php echo $this->pagination->getPagesLinks (); ?>
											<div class="limit"><?php echo $this->pagination->getResultsCounter (); ?></div>
										</div>
									</td>
								</tr>
							</tfoot>
							<tbody>
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
										<td><?php echo JHtml::_('grid.id', $i, intval($attachment->id)) ?></td>
										<td><?php echo $instance->getThumbnailLink() . ' ' . KunenaForumMessageAttachmentHelper::shortenFileName($attachment->filename, 10, 15) ?></td>
										<td><?php echo $this->escape($attachment->filetype); ?></td>
										<td><?php echo number_format ( intval ( $attachment->size ) / 1024, 0, '', ',' ) . ' KB'; ?></td>
										<td><?php echo isset($width) && isset($height) ? $width . ' x ' . $height  : '' ?></td>
										<td><?php echo $this->escape($message->name); ?></td>
										<td><?php echo $this->escape($message->subject); ?></td>
										<td><?php echo intval($attachment->id); ?></td>
									</tr>
								<?php
								$i++;
								$k = 1 - $k;
								}
								?>
							</tbody>
						</table>
					</form>
				</div>
				<div class="pull-right small">
					<?php echo KunenaVersion::getLongVersionHTML (); ?>
				</div>
			</div>
		</div>
	</div>
</div>

