<?php

namespace Drupal\friend_flag\Controller;

use Drupal\Core\Controller\ControllerBase;

class FriendFlagController extends ControllerBase {

  public function showFriends() {
    return array(
      '#markup' => friend_friend_list(),
    );
  }
}
