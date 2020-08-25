-- /*******************************************************
-- *
-- * civicrm_membersonlyevent
-- *
-- * Stores members-only event configurations
-- *
-- *******************************************************/
CREATE TABLE IF NOT EXISTS `membersonlyevent` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  ,
     `event_id` int unsigned NOT NULL   COMMENT 'Foreign key for the Event',
     `purchase_membership_button` tinyint   DEFAULT 0 COMMENT 'Should we provide membership purchase button when access to event denied ?',
     `notice_for_access_denied` text   DEFAULT NULL COMMENT 'Notice message to show to the user when the access to members-only event denied.',
     `purchase_membership_button_label` varchar(255)   DEFAULT NULL COMMENT 'Purchase membership button label if it is enabled',
     `purchase_membership_link_type` int   DEFAULT 0 COMMENT '0: contribution page, 1: custom URL',
     `contribution_page_id` int unsigned   DEFAULT NULL COMMENT 'Foreign key for the Contribution page',
     `purchase_membership_url` varchar(3000)   DEFAULT NULL COMMENT 'Purchase membership page URL',
    PRIMARY KEY ( `id` ),
    CONSTRAINT FK_civicrm_membersonlyevent_event_id
      FOREIGN KEY (`event_id`) REFERENCES `civicrm_event`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_membersonlyevent_contribution_page_id
      FOREIGN KEY (`contribution_page_id`) REFERENCES `civicrm_contribution_page`(`id`) ON DELETE SET NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * membersonlyevent_event_membership_type
-- *
-- * Joining table for members-only event and allowed membership types,
-- * In other words, this is where allowed membership types for members-only
-- * event are stored.
-- *
-- *******************************************************/
CREATE TABLE IF NOT EXISTS `membersonlyevent_event_membership_type` (
     `members_only_event_id` int unsigned NOT NULL   COMMENT 'Members-only event ID.',
     `membership_type_id` int unsigned NOT NULL   COMMENT 'Allowed Membership Type ID.',
    INDEX `index_event_id_membership_type_id`(members_only_event_id, membership_type_id),
    CONSTRAINT FK_membersonlyevent_event_membership_type_members_only_event_id
      FOREIGN KEY (`members_only_event_id`) REFERENCES `membersonlyevent`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_membersonlyevent_event_membership_type_membership_type_id
      FOREIGN KEY (`membership_type_id`) REFERENCES `civicrm_membership_type`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
