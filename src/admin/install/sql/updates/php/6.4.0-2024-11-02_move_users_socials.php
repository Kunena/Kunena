<?php
/**
 * Kunena Component
 *
 * @package        Kunena.Installer
 *
 * @copyright      Copyright (C) 2008 - @currentyear@ Kunena Team. All rights reserved.
 * @license        https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Kunena\Forum\Libraries\User\KunenaUserSocials;

/**
 *  Handle the data by group of 100 lines
 * 
 * @param array $array
 * @param integer $numRows
 * @param integer $size
 */
function chunk($array, $numRows, $size) {
    $result = array();
    for ($i = 0; $i < $numRows; $i += $size) {
        $result[] = array_slice($array, $i, $size);
    }
    
    return $result['0'];
}

/**
 * @param $parent
 *
 * @return array
 * @throws Exception
 * @since Kunena 6.4.0
 */
function kunena_640_2024_11_02_move_users_socials($parent) {   
    $db     = Factory::getContainer()->get('DatabaseDriver');

    // Check if the state of update if from K6.3
    $db->setQuery("SELECT state FROM #__kunena_version ORDER BY `id` DESC", 0, 1);
    $updateState = $db->loadResult();

    if (!empty($updateState) && version_compare($installed, '6.4.0-BETA1', '<')) {
    // Get the number of lines in table #__kunena_users    
    $query  = $db->createQuery()
        ->select(array('userid', 'banned'))
        ->from($db->quoteName('#__kunena_users'))
        ->where($db->quoteName('banned') . '= ' . $db->quote('1000-01-01 00:00:00')
    );
    $db->setQuery($query);
    $db->execute();    
    $numRows = $db->getNumRows(); 
    
    $query  = $db->createQuery()
        ->select($db->quoteName(array('userid','banned','digg','telegram','vk','microsoft','skype','x_social','facebook','myspace','google','github','linkedin','linkedin_company','instagram','qqsocial','blogspot','youtube','reddit','bsky_app','flickr','apple','qzone','vimeo','whatsapp','weibo','wechat','yim','pinterest','ok')))
        ->from($db->quoteName('#__kunena_users'))
        ->where($db->quoteName('banned') . '= ' . $db->quote('1000-01-01 00:00:00')
    );
    $db->setQuery($query);
    $db->execute();
    $dataResults = (array) $db->loadAssocList();
    
    foreach (chunk($dataResults, $numRows, 100) as $line) {
        $result = ArrayHelper::toObject($line);
        $socials = KunenaUserSocials::getInstance($result->userid, false);
        
        if (isset($result->x_social)) {
            $socials->x_social->value = $result->x_social;
        }
        
        if (isset($result->facebook)) {
            $socials->facebook->value = $result->facebook;
        }
        
        if (isset($result->myspace)) {
            $socials->myspace->value = $result->myspace;
        }
        
        if (isset($result->linkedin)) {
            $socials->linkedin->value = $result->linkedin;
        }
        
        if (isset($result->linkedin_company)) {
            $socials->linkedin_company->value = $result->linkedin_company;
        }
        
        if (isset($result->digg)) {
            $socials->digg->value = $result->digg;
        }
        
        if (isset($result->skype)) {
            $socials->skype->value = $result->skype;
        }
        
        if (isset($result->yim)) {
            $socials->yim->value = $result->yim;
        }
        
        if (isset($result->google)) {
            $socials->google->value = $result->google;
        }
        
        if (isset($result->github)) {
            $socials->github->value = $result->github;
        }
        
        if (isset($result->microsoft)) {
            $socials->microsoft->value = $result->microsoft;
        }
        
        if (isset($result->blogspot)) {
            $socials->blogspot->value = $result->blogspot;
        }
        
        if (isset($result->flickr)) {
            $socials->flickr->value = $result->flickr;
        }
        
        if (isset($result->instagram)) {
            $socials->instagram->value = $result->instagram;
        }
        
        if (isset($result->qqsocial)) {
            $socials->qqsocial->value = $result->qqsocial;
        }
        
        if (isset($result->qzone)) {
            $socials->qzone->value = $result->qzone;
        }
        
        if (isset($result->weibo)) {
            $socials->weibo->value = $result->weibo;
        }
        
        if (isset($result->wechat)) {
            $socials->wechat->value = $result->wechat;
        }
        
        if (isset($result->vk)) {
            $socials->vk->value = $result->vk;
        }
        
        if (isset($result->telegram)) {
            $socials->telegram->value = $result->telegram;
        }
        
        if (isset($result->apple)) {
            $socials->apple->value = $result->apple;
        }
        
        if (isset($result->vimeo)) {
            $socials->vimeo->value = $result->vimeo;
        }
        
        if (isset($result->whatsapp)) {
            $socials->whatsapp->value = $result->whatsapp;
        }
        
        if (isset($result->youtube)) {
            $socials->youtube->value = $result->youtube;
        }
        
        if (isset($result->ok)) {
            $socials->ok->value = $result->ok;
        }
        
        if (isset($result->pinterest)) {
            $socials->pinterest->value = $result->pinterest;
        }
        
        if (isset($result->reddit)) {
            $socials->reddit->value = $result->reddit;
        }
        
        if (isset($result->bsky_app)) {
            $socials->bluesky_app->value = $result->bsky_app;
        }
        
        $socials->save();
    }

	return array('action' => '', 'name' => Text::_('COM_KUNENA_INSTALL_640_UPDATE_USERS_SOCIALS'), 'success' => true);
    }
}
