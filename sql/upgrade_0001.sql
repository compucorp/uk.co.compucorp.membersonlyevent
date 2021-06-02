SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `membersonlyevent_event_group`;

SET FOREIGN_KEY_CHECKS = 1;

-- /*******************************************************
-- *
-- * membersonlyevent_event_group
-- *
-- * Joining table for members-only event and allowed groups
-- *
-- *******************************************************/
CREATE TABLE `membersonlyevent_event_group`
(
    `id`                    int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique EventGroup ID',
    `members_only_event_id` int unsigned NOT NULL COMMENT 'Members-only event ID.',
    `group_id`              int unsigned NOT NULL COMMENT 'Allowed Group ID.',
    PRIMARY KEY (`id`),
    INDEX `index_event_id_group_id` (
                                     members_only_event_id,
                                     group_id
        ),
    CONSTRAINT FK_membersonlyevent_event_group_members_only_event_id FOREIGN KEY (`members_only_event_id`) REFERENCES `membersonlyevent` (`id`) ON DELETE CASCADE,
    CONSTRAINT FK_membersonlyevent_event_group_group_id FOREIGN KEY (`group_id`) REFERENCES `civicrm_group` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- /*******************************************************
-- *
-- * membersonlyevent
-- *
-- * Add is_groups_only column
-- *
-- *******************************************************/
ALTER TABLE `membersonlyevent` ADD `is_groups_only` TINYINT NOT NULL DEFAULT '0' COMMENT 'Should we check groups instead of membership types ?' AFTER `purchase_membership_url`;
