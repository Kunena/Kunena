ALTER TABLE `#__kunena_topics` MODIFY COLUMN `params` text NULL;
ALTER TABLE `#__kunena_user_topics` MODIFY COLUMN `params` text NULL;
ALTER TABLE `#__kunena_announcement` MODIFY COLUMN `created` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_announcement` MODIFY COLUMN `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_announcement` MODIFY COLUMN `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_categories` MODIFY COLUMN `checked_out_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_categories` MODIFY COLUMN `ordering` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__kunena_polls` MODIFY COLUMN `polltimetolive` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_polls_users` MODIFY COLUMN `lasttime` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_rate` MODIFY COLUMN `time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_thankyou` MODIFY COLUMN `time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_users` MODIFY COLUMN `banned` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_users_banned` MODIFY COLUMN `expiration` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_users_banned` MODIFY COLUMN `created_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__kunena_users_banned` MODIFY COLUMN `modified_time` datetime NULL DEFAULT NULL;
UPDATE `#__kunena_announcement` SET `created` = CASE WHEN `created` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `created` END;
UPDATE `#__kunena_announcement` SET `publish_up` = CASE WHEN `publish_up` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `publish_up` END;
UPDATE `#__kunena_announcement` SET `publish_down` = CASE WHEN `publish_down` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `publish_down` END;
UPDATE `#__kunena_categories` SET `checked_out_time` = CASE WHEN `checked_out_time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `checked_out_time` END;
UPDATE `#__kunena_polls` SET `polltimetolive` = CASE WHEN `polltimetolive` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `polltimetolive` END;
UPDATE `#__kunena_polls_users` SET `lasttime` = CASE WHEN `lasttime` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `lasttime` END;
UPDATE `#__kunena_rate` SET `time` = CASE WHEN `time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `time` END;
UPDATE `#__kunena_thankyou` SET `time` = CASE WHEN `time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `time` END;
UPDATE `#__kunena_users` SET `banned` = CASE WHEN `banned` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `banned` END;
UPDATE `#__kunena_users_banned` SET `expiration` = CASE WHEN `expiration` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `expiration` END;
UPDATE `#__kunena_users_banned` SET `created_time` = CASE WHEN `created_time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `created_time` END;
UPDATE `#__kunena_users_banned` SET `modified_time` = CASE WHEN `modified_time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `modified_time` END;
INSERT INTO `#__mail_templates` (`template_id`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`)
VALUES ('com_kunena.reply', '', 'COM_CONFIG_SENDMAIL_SUBJECT', 'COM_CONFIG_SENDMAIL_BODY', '', '',
        '{"tags":["mail", "subject", "message", "messageUrl", "once"]}');
INSERT INTO `#__mail_templates` (`template_id`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`)
VALUES ('com_kunena.replymoderator', '', 'COM_CONFIG_SENDMAIL_SUBJECT', 'COM_CONFIG_SENDMAIL_BODY', '', '',
        '{"tags":["mail", "subject", "message", "messageUrl", "once"]}');
INSERT INTO `#__mail_templates` (`template_id`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`)
VALUES ('com_kunena.report', '', 'COM_CONFIG_SENDMAIL_SUBJECT', 'COM_CONFIG_SENDMAIL_BODY', '', '',
        '{"tags":["mail", "subject", "message", "messageUrl", "once"]}');
ALTER TABLE `#__kunena_users` ADD `pinterest` VARCHAR(75) NULL AFTER `yim`;
ALTER TABLE `#__kunena_users` ADD `reddit` VARCHAR(75) NULL AFTER `pinterest`;
ALTER TABLE `#__kunena_categories` CHANGE `allow_anonymous` `allowAnonymous` tinyint(4);
ALTER TABLE `#__kunena_categories` CHANGE `post_anonymous` `postAnonymous` tinyint(4);
ALTER TABLE `#__kunena_categories` CHANGE `allow_polls` `allowPolls` tinyint(4);
ALTER TABLE `#__kunena_categories` CHANGE `parent_id` `parentid` int(11);
ALTER TABLE `#__kunena_categories` CHANGE `pub_recurse` `pubRecurse` tinyint(4);
ALTER TABLE `#__kunena_categories` CHANGE `admin_recurse` `adminRecurse` tinyint(4);
ALTER TABLE `#__kunena_categories` CHANGE `pub_access` `pubAccess` int(11);
ALTER TABLE `#__kunena_categories` CHANGE `admin_access` `adminAccess` int(11);
ALTER TABLE `#__kunena_categories` CHANGE `topic_ordering` `topicOrdering` varchar(16);
ALTER TABLE `#__kunena_categories` CHANGE `allow_ratings` `allowRatings` tinyint(4);
ALTER TABLE `#__kunena_ranks` CHANGE `rank_title` `rankTitle` varchar(255);
ALTER TABLE `#__kunena_ranks` CHANGE `rank_image` `rankImage` varchar(255);
ALTER TABLE `#__kunena_ranks` CHANGE `rank_special` `rankSpecial` tinyint(1);
ALTER TABLE `#__kunena_ranks` CHANGE `rank_min` `rankMin` mediumint(8);
ALTER TABLE `#__kunena_ranks` CHANGE `rank_id` `rankId` mediumint(8);
ALTER TABLE `#__kunena_version` ADD `sampleData` boolean not null default 1 AFTER `versionname`;