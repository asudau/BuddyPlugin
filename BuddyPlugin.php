<?php

class BuddyPlugin extends StudipPlugin implements SystemPlugin 
{
	public function __construct() {
		
		$page = basename($_SERVER['PHP_SELF']);
	
	        if ($page === 'contact.php' || $page === 'sms_send.php' || $page === 'seminar_main.php') {

			$stmt = DBManager::get()->prepare("SELECT sub.user_id FROM seminar_user su
				LEFT JOIN seminare s USING (Seminar_id)
				LEFT JOIN seminar_user sub ON (s.Seminar_id = sub.Seminar_id)
				WHERE su.user_id = ? AND su.user_id <> sub.user_id");
			
			$stmt->execute(array($GLOBALS['user']->id));

			$stmt_insert = DBManager::get()->prepare("INSERT INTO contact
				(owner_id, user_id)
				VALUES (?, ?)");
		
			
			while ($data = $stmt->fetch()) {
			
				$result = DBManager::get()->prepare("SELECT * FROM contact WHERE user_id = ? AND owner_id = ?;");
				$result->execute(array($data['user_id'], $GLOBALS['user']->id));
				if($result->fetch() == 0){
				$stmt_insert->execute(array($GLOBALS['user']->id, $data['user_id']));
				}				
			}
		}
	}
}