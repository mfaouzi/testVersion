INSERT INTO `Category` (`parent_id`, `code`, `created`, `root`, `lvl`, `lft`, `rgt`)
SELECT parent_id, code, created, root, lvl, lft, rgt FROM pim_catalog_category;

INSERT INTO `akeCategory_Translation` (foreign_key, label, locale)
SELECT foreign_key, label, locale FROM pim_catalog_category_translation;