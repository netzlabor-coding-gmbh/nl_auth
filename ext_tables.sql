#
# Add SQL definition of database tables
#

#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
  tx_nlauth_user_redirectPid TINYTEXT
);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
  tx_nlauth_user_redirectPid TINYTEXT,
  tx_nlauth_user_confirmedAt INT(10) UNSIGNED NOT NULL DEFAULT '0',
  tx_nlauth_user_approvedAt INT(10) UNSIGNED NOT NULL DEFAULT '0',
  tx_nlauth_user_declinedAt INT(10) UNSIGNED NOT NULL DEFAULT '0'
);
