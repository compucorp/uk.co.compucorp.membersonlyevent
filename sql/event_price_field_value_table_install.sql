SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `membersonlyevent_event_price_field_value`;

SET FOREIGN_KEY_CHECKS=1;

-- /*******************************************************
-- *
-- * membersonlyevent_event_price_field_value
-- *
-- * Join table for storing price field value for members only event
-- *
-- *******************************************************/
CREATE TABLE `membersonlyevent_event_price_field_value`(
    `members_only_event_id` int unsigned NOT NULL COMMENT 'Members-only event ID.',
    `price_field_value_id`  int unsigned NOT NULL COMMENT 'Price Field Value ID.',
    CONSTRAINT FK_membersonlyevent_event_price_field_value_event_id FOREIGN KEY (`members_only_event_id`) REFERENCES `membersonlyevent` (`id`) ON DELETE CASCADE,
    CONSTRAINT FK_membersonlyevent_event_price_field_value_value_id FOREIGN KEY (`price_field_value_id`) REFERENCES `civicrm_price_field_value` (`id`) ON DELETE CASCADE
);
