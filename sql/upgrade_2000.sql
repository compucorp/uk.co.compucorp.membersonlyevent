-- /*******************************************************
-- *
-- * membersonlyevent_select_price_field
-- *
-- * Join table for storing selected price field for members only event
-- *
-- *******************************************************/
CREATE TABLE `membersonlyevent_select_price_field`(
    `members_only_event_id` int unsigned NOT NULL COMMENT 'Members-only event ID.',
    `price_field_id`        int unsigned NOT NULL COMMENT 'Selected Price Field ID.',
    CONSTRAINT FK_membersonlyevent_select_price_field_members_only_event_id FOREIGN KEY (`members_only_event_id`) REFERENCES `membersonlyevent` (`id`) ON DELETE CASCADE,
    CONSTRAINT FK_membersonlyevent_select_price_field_price_field_id FOREIGN KEY (`price_field_id`) REFERENCES `civicrm_price_field` (`id`) ON DELETE CASCADE
);


