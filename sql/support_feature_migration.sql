-- Run once against an existing FitCampus database.
-- Fresh imports of fitcampus_db.sql already include these columns.
ALTER TABLE `feedback`
  ADD COLUMN `Attachment_Path` varchar(255) DEFAULT NULL AFTER `Description`,
  ADD COLUMN `Requested_End_Date` date DEFAULT NULL AFTER `Attachment_Path`;