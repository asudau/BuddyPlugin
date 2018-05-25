<?php

class BuddyPlugin extends StudipPlugin implements SystemPlugin 
{
	public function __construct() {
		
        parent::__construct();
        NotificationCenter::addObserver($this, "add_contacts", "UserDidEnterCourse");
        NotificationCenter::addObserver($this, "remove_contacts", "UserDidLeaveCourse");

	}
    
    public function add_contacts($object, $seminar_id, $user_id) {
        /* für alle Nutzer in dem seminar wird der neue user als kontakt hinzugefügt und umgekehrt */
        $members = CourseMember::findByCourse($seminar_id);
        foreach ($members as $member){
            if ($member['user_id'] != $user_id){
                if (!Contact::findBySQL("owner_id = '" . $member['user_id'] . "' AND user_id = '" . $user_id . "'")){
                    $contact = new Contact();
                    $contact->owner_id = $member['user_id'];
                    $contact->user_id = $user_id;    
                    $contact->store();
                }
                if (!Contact::findBySQL("owner_id = '" . $user_id . "' AND user_id = '" . $member['user_id'] . "'")){
                    $contact2 = new Contact();
                    $contact2->owner_id = $user_id;
                    $contact2->user_id = $member['user_id'];    
                    $contact2->store();
                } 
            }
        }
    }
    
    public function remove_contacts($object, $seminar_id, $user_id) {
        /* für alle nutzer in dem seminar werden */
        $members = CourseMember::findByCourse($seminar_id);
        foreach ($members as $member){
            if ($member['user_id'] != $user_id){
                //wenn die Nutzer nicht in weitere Veranstatungen gemeinsam eingetragen sind, lösche Adressbucheinträge
                if ($this->joint_courses($member['user_id'], $user_id) < 1){
                    $contact = Contact::find(array($user_id, $member['user_id']));
                    PageLayout::postMessage(MessageBox::success(sprintf(_('ids: %s,  %s'), $user_id, $member['user_id'])));
                    if ($contact) {
                        $result = $contact->delete();
                    }
                    $contact2 = Contact::find(array($member['user_id'], $user_id));
                    if ($contact2) {
                        $result = $contact2->delete();
                    }
                }
            } 
        }
    }
    
    private function joint_courses($usera, $userb){
        $joint_courses = 0;
        $user_a = new User($usera);
        $coursesa = $user_a->course_memberships;
        foreach ($coursesa as $course){
            PageLayout::postMessage(MessageBox::success(sprintf(_('user: %s, seminar %s'),$userb, $course->seminar_id)));
            if (CourseMember::findBySQL("user_id = '" . $userb . "' AND Seminar_id = '" . $course->seminar_id . "'")){
                $joint_courses++;
            }
        } 
        PageLayout::postMessage(MessageBox::success($joint_courses));
        return $joint_courses;
    }
}