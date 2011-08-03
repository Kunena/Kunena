<?php
/**
 * Kunena Component
 * @package Kunena.Template.Default20
 * @subpackage Topic
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip');
?>
		<div id="kmodtopic" class="ksection">
			<h2 class="kheader"><a rel="kmod-detailsbox">Moderate Topic</a></h2>
			<p class="kheader-desc">Category: <strong>Welcome Mat</strong></p>
			<div class="kdetailsbox kmod-detailsbox" id="kmod-detailsbox" ><form name="myform" method="post" action="#">
				<ul class="kmod-postlist">
					<li>
						<ul class="kposthead">
							<li class="kposthead-replytitle"><h3>Ea torquatos suscipit vituperatoribus quo</h3></li>
						</ul>
						<div class="kmod-container">
							<p>Selected Message:</p>
							<div class="kmoderate-message">
								<h4>Ea torquatos suscipit vituperatoribus quo</h4>
								<div class="kmessage-timeby"><span title="15 Oct 2010 08:29" class="kmessage-time">
								Posted 4 months, 1 week ago</span>
								<span class="kmessage-by">by <a rel="nofollow" title="" href="#" class="kwho-user">tester</a></span></div>
								<div class="kmessage-avatar"><img alt="" src="images/avatar.png"></div>
								<div class="kmessage-msgtext">Ea torquatos suscipit vituperatoribus quo, eu mea inimicus gubergren. Te clita doctus facilis ius, vix movet nonummy luptatum an. Ne alterum labores reformidans vim, est id porro ponderum scriptorem, et quem congue cetero nam. Amet mucius ne sed, an magna explicari eum. Veniam maluisset definieb...</div>
								<div class="clr"></div>
							</div>

							<p>
								Moderate this User:
								<strong><a rel="nofollow" title="" href="#">tester (63)</a></strong>
							</p>

							<ul>
								<li><label for="kmoderate-mode-selected" class="hasTip" title="Move only selected message :: "><input type="radio" value="0" checked="checked" name="mode" id="kmoderate-mode-selected">Move only selected message</label></li>
								<li><label for="kmoderate-mode-newer" class="hasTip" title="Move selected message and all 6 messages posted after it :: "><input type="radio" value="2" name="mode" id="kmoderate-mode-newer">Move selected message and all 6 messages posted after it</label></li>
							</ul>
							<br/>

							<div class="modcategorieslist">
								<label for="kmod_categories1">Target Category:</label>
								<select class="kinputbox kmove_selectbox hasTip" id="kmod_categories1" name="targetcat[1]" title="Target Category :: New Topic Category">
									<option disabled="disabled" value="1">Main Forum</option>
									<option selected="selected" value="2">...&nbsp;Welcome Mat</option>
									<option value="6">......&nbsp;Subforum1</option>
									<option value="7">......&nbsp;Subforum2</option>
									<option value="8">......&nbsp;Subforum3</option>
									<option value="3">...&nbsp;Suggestion Box</option>
									<option disabled="disabled" value="4">Top Category 1</option>
									<option value="5">...&nbsp;Te fabellas pericula incorrupte cum</option>
								</select>

							</div>

							<div class="modtopicslist">
								<label for="kmod_targettopic1">Target Topic:</label>
								<select class="kinputbox hasTip" id="kmod_targettopic1" name="targettopic[1]" title="Target Topic :: New Post Topic">
									<option selected="selected" value="0">Move Topic Into Another Category</option>
									<option value="-1">Manually Enter Topic ID</option>
									<option value="7">Nam quas molestie adolescens no</option>
									<option value="6">Eum ut nihil salutandi laboramus</option>
									<option value="5">Est lorem utroque deleniti te</option>
									<option value="4">Ferri albucius appareat pro id</option>
									<option value="3">Pro facer audiam vituperatoribus ei</option>
									<option value="2">Nusquam intellegam reprehendunt at nam</option>
									<option value="1">Welcome to Kunena!</option>
								</select>
							</div>

							<div class="kmod_subject">
								<label for="kmod_topicsubject1">New Subject:</label>
								<input id="kmod_topicsubject1" type="text" value="Ea torquatos suscipit vituperatoribus quo" name="subject[1]" class="input hasTip" size="50" title="New Subject :: Enter New Subject" />
							</div>

							<div class="clr"></div>
							<label for="kmod_shadow1" class="hasTip" title="Leave shadow topic pointing to new location :: "><input id="kmod_shadow1" type="checkbox" value="1" name="shadow[1]">Leave shadow topic pointing to new location</label>
						</div>
					</li>

				</ul>

				<div class="kpost-buttons">
					<button title="Click here to save" type="submit" class="kbutton"> Submit </button>
					<button onclick="javascript:window.history.back();" title="Click here to cancel" type="button" class="kbutton"> Cancel </button>
				</div>

				<div class="clr"></div>
			</form></div>
		</div>