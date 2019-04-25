# friend flag
Friend Module for Drupal
Very simple "friend" module built upon Flag module. Then install this module. Need to create a user flag called friend. It will create block for friend activity. The block is table with two columns: friend requests (with a link to add the user as a friend), and a friends column. Principle is a user clicks the flag on another user, that is a friend request. If two users each click the friend flag on the other's profile page, they are friends.

Screenshots available at https://www.drupal.org/sandbox/bryantt/2987656

For most users, you'll only want their "friend" block visible to them and on a certain page. If this is the case, add the block via Block Layout and restrict access via the Block Layout under structure.

Note this assumes your url for each account is yourdomainname.com/user/theirUserID. If you have additional "/"s, add one to the $arg[] for each additional / in the url before the user id. 
