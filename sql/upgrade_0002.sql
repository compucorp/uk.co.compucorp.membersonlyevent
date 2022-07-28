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

-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Add is_showing_login_block column
-- * Add block_type column
-- * Add login_block_message column
-- *
-- *******************************************************/
ALTER TABLE `membersonlyevent` ADD `is_showing_login_block` tinyint DEFAULT 0 COMMENT 'This allows you to choose a Login block to display and add a custom message to be displayed on the block for anonymous users' AFTER `notice_for_access_denied`;
ALTER TABLE `membersonlyevent` ADD `block_type` int unsigned DEFAULT 1 COMMENT 'Login block type' AFTER `is_showing_login_block`;
ALTER TABLE `membersonlyevent` ADD `login_block_message` text DEFAULT NULL COMMENT 'Login block message to show to the user when access to members-only event denied.' AFTER `block_type`;

-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Add is_showing_purchase_membership_block column
-- * Add purchase_membership_body_text column
-- *
-- *******************************************************/
ALTER TABLE `membersonlyevent` ADD `is_showing_purchase_membership_block` tinyint DEFAULT 0 COMMENT 'This allows you to add a label, custom message and  link to be displayed on the event.' AFTER `login_block_message`;
ALTER TABLE `membersonlyevent` ADD `purchase_membership_body_text` text DEFAULT NULL COMMENT 'Custom message to show to the user when access to members-only event denied.' AFTER `purchase_membership_button_label`;

-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Migrate purchase_membership_button column values to is_showing_purchase_membership_block column
-- * Drop purchase_membership_button column
-- *
-- *******************************************************/
UPDATE `membersonlyevent` SET `is_showing_purchase_membership_block` = `purchase_membership_button`;
ALTER TABLE `membersonlyevent` DROP COLUMN purchase_membership_button;
