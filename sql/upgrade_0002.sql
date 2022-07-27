-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Rename is_groups_only column to event_access_type
-- *
-- *******************************************************/
ALTER TABLE `membersonlyevent` CHANGE `is_groups_only` `event_access_type` int unsigned DEFAULT 1 COMMENT 'Should we check event access based on user authentication, group or membership type?' AFTER `purchase_membership_url`;

-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Increment event_access_type column to start from 1
-- *
-- *******************************************************/
UPDATE `membersonlyevent` SET `event_access_type` = `event_access_type` + 1;

-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Add is_showing_custom_access_denied_message column
-- *
-- *******************************************************/
ALTER TABLE `membersonlyevent` ADD `is_showing_custom_access_denied_message` tinyint DEFAULT 0 COMMENT 'This allows you to add a custom access denied message for members-only event.' AFTER `purchase_membership_button`;
