SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `membersonlyevent_entity_price_field_value`;

SET FOREIGN_KEY_CHECKS=1;

-- /*******************************************************
-- *
-- * membersonlyevent_entity_price_field_value
-- *
-- * Join table for storing selected entities for price field value
-- *
-- *******************************************************/
CREATE TABLE `membersonlyevent_entity_price_field_value` (
    `id`                               int unsigned NOT NULL AUTO_INCREMENT,
    `entity_table`                     varchar(64)  COMMENT 'physical tablename for entity being joined to file, e.g. civicrm_membership_type',
    `entity_id`                        int unsigned COMMENT 'FK to entity table specified in entity_table column.',
    `price_field_value_id`             int unsigned COMMENT 'Foreign key for the civicrm_price_field_value',
    PRIMARY KEY (`id`),
    KEY `entity_table_entity_id` (`entity_table`,`entity_id`),
    KEY `price_field_value_id` (`price_field_value_id`),
    CONSTRAINT `FK_membersonlyevent4_price_field_value_id` FOREIGN KEY (`price_field_value_id`) REFERENCES `civicrm_price_field_value` (`id`) ON DELETE CASCADE
)
