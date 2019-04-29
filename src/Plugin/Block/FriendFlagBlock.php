<?php

namespace Drupal\friend_flag\Plugin\Block;

use Drupal\user\Entity\User;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;

/**
 * @Block(
 *   id = "friend__flag_block",
 *   admin_label = @Translation("Friend")
 * )
 */
class FriendFlagBlock extends Blockbase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return array(
      '#markup' => $this->_populate_markup(),
      '#attached' => array(
        'library' => array(
          'friend_flag/friend_flag',
        ),
      ),
    );
  }

  /**
  * {@inheritdoc}
  */
  public function getCacheMaxAge() {
      return 0;
  }

  /**
  * We'll query the database to create a html string for a table with two columns.
  * Friend Requests Received column means the user was the target of the 'friend'
  * flag.  
  */ 
  private function _populate_markup() {
  $user = User::load(\Drupal::currentUser()->id());
	$currentUserID = $user->get('uid')->value;
	$home_url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->ToString();

    
	$friendString = "<table class='friendTable'><tr>";
	$database = \Drupal::database();


  // Begin 'Friend Requests Received' Column.  Requests received are the rows in the flagging table where entity_id is the current user
  $friendString .= "<td valign='top' class='friendRequests'><b>Friend Requests Received</b></br><ul>";
	$resultReceived = $database->select('flagging', 'f')
	  ->fields('f')
	  ->condition('entity_id', $currentUserID)
	  ->condition('flag_id', 'friend')
	  ->orderby('created', 'DESC')
	  ->execute();
	//If no results, leave a message for the column
	$resultReceived->allowRowCount = TRUE;
    		// Now we loop through results and return a link to the user who sent the request, a link to friend, and 'new' if within past two days.
			// The counter is to keep track of the requests received who aren't friends.  If counter is 0, leave a message
			$Count = 0;
			foreach ($resultReceived as $recordReceived)  {
	  		//We further limit to remove people who are already friends
	  		//query uid = strCurrentUser and entity_id = recordReceived->uid.  If Not TRUE, do $friendString section
				$Count += 1;
	  			$alreadyFriendsCount = $database->query("SELECT * FROM {flagging} WHERE uid = :uid and entity_id = :entity_id", [':uid' => $currentUserID, 'entity_id' => $recordReceived->uid]);
	  			$alreadyFriendsCount->allowRowCount = TRUE;
	  			if($alreadyFriendsCount->rowCount() == 0){
	  				$Friend_ID = $recordReceived->uid;
	  				$Created_Timestamp = $recordReceived->created;
	  				$Friend_Name = $database->query("SELECT name FROM {users_field_data} WHERE uid = :uid", array(':uid' => $Friend_ID))->fetchField();



	  			//if less that two days print "new" afterwards in red.
					$intTimeAgoFriendReceived = time() - $Created_Timestamp;
					if ($intTimeAgoFriendReceived < 172800){
						$strNew = "&nbsp;<b>(New)</b>&nbsp;";
					}else{
						$strNew = "";
					}

					//generate url to create a friend link
					$url = Url::fromRoute('flag.action_link_flag',['flag' => 'friend', 'entity_id' => $Friend_ID]);
					$token = \Drupal::csrfToken()->get($url->getInternalPath());
					$url->setOptions(['absolute' => TRUE, 'query' => ['token' => $token]]);
					$friend_url = $url->toString();

	    			$friendString .= "<li><div class='flag-friend-name'><a href='" . $home_url . "user/" . $Friend_ID ."'>" . $Friend_Name . "</a></div> " . $strNew . 
              "<div class='flag flag-friend js-flag-friend-" . $Friend_ID ." action-flag'><a title='' href='" . $friend_url . 
              "' class='use-ajax' rel='nofollow'>Add as friend</a></div></li>";
				}else{
					$Count -= 1;
				}
	  }

	if ($Count == 0){
		$friendString .= "No pending friend requests";
	}
    
	$friendString .= "</td>";

	$friendString .= "<td valign='top' class='friendsAccepted'><b>Friends</b></br><ul>";

  //Friends column, each user has 'flagged' the other, so are now friends.
  //Start by pulling in records where current UID = uid and getting the entity_id from the table.  These are people the current user has flagged as a friend.
	
	$result = $database->select('flagging', 'f')
	  ->fields('f')
	  ->condition('uid', $currentUserID)
	  ->condition('flag_id', 'friend')
	  ->orderby('created', 'DESC')
	  ->execute();
    // Now we loop through results and limit  further to those where the current user has also been flagged.  Now the 'entity_id' is the friend's id and 'uid' is the current user.
	foreach ($result as $record)  {
	  $Friend_UID = $record->entity_id;
	  $result2 = $database->select('flagging', 'f2')
	  ->fields('f2')
	  ->condition('entity_id', $currentUserID)
	  ->condition('uid', $Friend_UID)
	  ->condition('flag_id', 'friend')
	  ->orderby('created', 'DESC')
	  ->execute();
	  
	  
	  foreach ($result2 as $record2)  {
	    $Friend_ID = $record2->uid;
	    $Created_Timestamp = $record2->created;
	    $Friend_Name = $database->query("SELECT name FROM {users_field_data} WHERE uid = :uid", array(':uid' => $Friend_ID))->fetchField();

		//if less that two days print "new" afterwards in red.
		$intTimeAgoFriendCreated = time() - $Created_Timestamp;
		if ($intTimeAgoFriendCreated < 172800){
			$strNew = " <b>(New)</b> ";
		}else{
			$strNew ="";
		}
	    $friendString .= "<li><a href='". $home_url . "user/" . $Friend_ID ."'>" . $Friend_Name . "</a>" . $strNew . "</li>";
	    }
	  }
  $friendString .= "</td></tr></table>";
  $tag_array = array(
    'a',
    'b',
    'br',
    'div',
    'li',
    'table',
    'td',
    'tr',
    'ul',
  );
  $friendString = Xss::filter($friendString, $tag_array);
  return $friendString;

  }

}



