create view  `cvsponsors` AS 
select `ch`.`child_1` AS `child`,`ch`.`child_id_2` AS `childid`,`ch`.`entity_id` AS `entity_id`,
`cb`.`contact_id` AS `contact_id`,`cb`.`receive_date` AS `receive_date`,`cb`.`total_amount` AS `total_amount`,
`cb`.`fee_amount` AS `fee_amount`,`cb`.`net_amount` AS `net_amount`,`cb`.`trxn_id` AS `trxn_id`,
`cb`.`check_number` AS `check_number`,`cb`.`contribution_recur_id` AS `contribution_recur_id`,
`cr`.`frequency_unit` AS `frequency_unit`,`ct`.`sort_name` AS `sort_name`,`ct`.`display_name` AS `display_name`,
`ct`.`hash` AS `hash`,`em`.`email` AS `email`,`ct`.`first_name` AS `first_name`,`ct`.`last_name` AS `last_name` 
from (((
	(`15983_tgc_wordpress_civicrm`.`civicrm_value_child_sponsorship_1` `ch` 
	left join `15983_tgc_wordpress_civicrm`.`civicrm_contribution` `cb` 
	   on ((`ch`.`entity_id` = `cb`.`id`) and (cb.is_test = 0) and (cb.contribution_status_id = 1))) 
    left join `15983_tgc_wordpress_civicrm`.`civicrm_contribution_recur` `cr` 
       on((`cb`.`contribution_recur_id` = `cr`.`id`))) 
    left join `15983_tgc_wordpress_civicrm`.`civicrm_contact` `ct` on((`ct`.`id` = `cb`.`contact_id`))) 
    left join `15983_tgc_wordpress_civicrm`.`civicrm_email` `em` on(((`em`.`is_primary` = 1) and (`em`.`contact_id` = `ct`.`id`)))) ;

