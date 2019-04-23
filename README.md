# friend flag
Friend Module for Drupal
Very simple "friend" module built upon Flag module. Need to create a user flag called friend. Then install this module. It will create block for friend activity. The block is table with two columns: friend requests (with a link to add the user as a friend), and a friends column. Principle is a user clicks the flag on another user, that is a friend request. If two users each click the friend flag on the other's profile page, they are friends.

Screenshots available at https://www.drupal.org/sandbox/bryantt/2987656

For most users, you'll only want their "friend" block visible to them and on their profile page. If this is the case, add the block via Block Layout and then add this code to restrict access via PHP

$user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id()); 
$uid=$user->get('uid')->value; $current_path = \Drupal::service('path.current')->getPath(); 
$arg = explode('/', $current_path); 
if ($arg[1] == 'user' && $arg[2] == $uid) 
{ 
return TRUE; 
} else { 
return FALSE; 
}

Note this assumes your url for each account is yourdomainname.com/user/theirUserID. If you have additional "/"s, add one to the $arg[] for each additional / in the url before the user id. 
