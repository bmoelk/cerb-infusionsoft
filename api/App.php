<?php

if (class_exists('Extension_ContextProfileTab')):

require_once('Infusionsoft/infusionsoft.php');

class ProfileTab_InfusionSoft extends Extension_ContextProfileTab {
  function showTab($context, $context_id) {
    $tpl = DevblocksPlatform::getTemplateService();
    $active_worker = CerberusApplication::getActiveWorker();

    $tpl->assign('context', $context);
    $tpl->assign('context_id', $context_id);

    // Check permissions on this ticket for this worker

    if(0 != strcasecmp($context, CerberusContexts::CONTEXT_TICKET))
      return;

    if(null == ($ticket = DAO_Ticket::get($context_id)))
      return;

    $tpl->assign('csrf_token', $_SESSION['csrf_token']);

    // if(!$active_worker->isGroupMember($ticket->group_id))
    //   return;

    // // Load the message IDs for this ticket

    // $messages = DAO_Message::getMessagesByTicket($context_id);

    $requesters = $ticket->getRequesters();
//    error_log(var_export($requesters,true),3,'/var/www/cerb-7.0.4/logs/foo.log');
    $requester = array_shift($requesters);

    $contacts = Infusionsoft_DataService::findByField(new Infusionsoft_Contact(), 'email', $requester->email);
    $contact = array_shift($contacts);

    $convertToInt = function($str) {
      return (int)$str;
    };
    $groupIds = array_map($convertToInt, explode(',', $contact->Groups));

    $extractData = function($group){
      return $group->toArray();
    };
    $groups = array_map($extractData, Infusionsoft_DataService::query(new Infusionsoft_ContactGroup(), array('Id' => $groupIds)));

    // Template
    $tpl->assign('contact', $contact);
    $tpl->assign('groups', $groups);
    $tpl->display('devblocks:cerb-infusionsoft::tab.tpl');
  }

  function AddTag($tagID) {
    Infusionsoft_ContactService::addToGroup($contactID, $tagID);
  }
}
endif;


class InfusionSoftController extends DevblocksControllerExtension {
  const ID = 'bmoelk.infusionsoft.controller';

  function isVisible() {
    // The current session must be a logged-in worker to use this page.
    if(null == ($worker = CerberusApplication::getActiveWorker()))
      return false;
    return true;
  }

  /*
   * Request Overload
   */
  function handleRequest(DevblocksHttpRequest $request) {
    $stack = $request->path;
    array_shift($stack); // example

      @$action = array_shift($stack) . 'Action';

      switch($action) {
          case NULL:
              break;

          default:
          // Default action, call arg as a method suffixed with Action
        if(method_exists($this,$action)) {
          call_user_func(array(&$this, $action));
        }
              break;
      }

      exit;
  }

  function writeResponse(DevblocksHttpResponse $response) {
    return;
  }


  function find_tagAction() {
    $context_id = DevblocksPlatform::importGPC($_REQUEST['context_id'],'integer',0);

    if(empty($context_id))
      die();

    // Security
    if(null == ($active_worker = CerberusApplication::getActiveWorker()))
      die($translate->_('common.access_denied'));

    $tag = DevblocksPlatform::importGPC($_REQUEST['tag'],'string','');

    $extractTags = function($group){
      return array('id' => $group->Id, 'name' => $group->GroupName);
    };
    $matchingTags = array_map($extractTags, Infusionsoft_DataService::query(new Infusionsoft_ContactGroup(), array('GroupName' => '%'.$tag.'%')));

    header('Content-type: application/json');
    echo json_encode($matchingTags);

    exit;
  }

  function add_tagAction() {
    $context_id = DevblocksPlatform::importGPC($_REQUEST['context_id'],'integer',0);

    if(empty($context_id))
      die();

    // Security
    if(null == ($active_worker = CerberusApplication::getActiveWorker()))
      die($translate->_('common.access_denied'));

    $tagId = DevblocksPlatform::importGPC($_REQUEST['tag'],'integer',0);
    $contactId = DevblocksPlatform::importGPC($_REQUEST['contact_id'],'integer',0);

    if (($tagId == 0) || ($contactId == 0))
      die('Invalid Tag ID.');

    $result = array('status' => 'error', 'message' => 'Unknown error.');
    try {
      if (Infusionsoft_ContactService::addToGroup($contactId, $tagId) == 1) {
        $result['status'] = 'ok';
        $result['message'] = 'Tag added!';
      } else {
        $result['message'] = 'Error occurred invoking InfusionSoft. Please contact your system administrator.';
      }
    } catch (Exception $e) {
      $result['message'] = $e->getMessage();
    }
    header('Content-type: application/json');
    echo json_encode($result);

    exit;
  }

  function remove_tagAction() {
    $context_id = DevblocksPlatform::importGPC($_REQUEST['context_id'],'integer',0);

    if(empty($context_id))
      die();

    // Security
    if(null == ($active_worker = CerberusApplication::getActiveWorker()))
      die($translate->_('common.access_denied'));

    $tagId = DevblocksPlatform::importGPC($_REQUEST['tag'],'integer',0);
    $contactId = DevblocksPlatform::importGPC($_REQUEST['contact_id'],'integer',0);

    if (($tagId == 0) || ($contactId == 0))
      die('Invalid Tag ID.');

    $result = array('status' => 'error', 'message' => 'Unknown error.');
    try {
      if (Infusionsoft_ContactService::removeFromGroup($contactId, $tagId) == 1) {
        $result['status'] = 'ok';
        $result['message'] = 'Tag removed.';
      } else {
        $result['message'] = 'Error occurred invoking InfusionSoft. Please contact your system administrator.';
      }
    } catch (Exception $e) {
      $result['message'] = $e->getMessage();
    }
    header('Content-type: application/json');
    echo json_encode($result);

    exit;
  }

  function update_tagsAction(){
    $context_id = DevblocksPlatform::importGPC($_REQUEST['context_id'],'integer',0);

    if(empty($context_id))
      die();

    // Security
    if(null == ($active_worker = CerberusApplication::getActiveWorker()))
      die($translate->_('common.access_denied'));

    $tagDeltas = DevblocksPlatform::importGPC($_REQUEST['tag_deltas'],'string','');
    $contactId = DevblocksPlatform::importGPC($_REQUEST['contact_id'],'integer',0);

    if (($tagDeltas == '') || ($contactId == 0))
      die('Invalid Tag deltas.');

    $convertToCommands = function($delta){
      return array('operation' => $delta[0], 'tag_id' => substr($delta, 1));
    };

    $commands = array_map($convertToCommands, explode(',', $tagDeltas));

    $result = array('added' => array(), 'removed' => array(), 'errors' => array());
    foreach ($commands as $cmd) {
      try {
        switch($cmd['operation']) {
          case 'A':
            if (Infusionsoft_ContactService::addToGroup($contactId, $cmd['tag_id']) == 1) {
              array_push($result['added'], $cmd['tag_id']);
            } else {
              array_push($result['errors'], array_merge($cmd, array('message' => '')));
            };
            break;
          case 'R':
            if (Infusionsoft_ContactService::removeFromGroup($contactId, $cmd['tag_id']) == 1) {
              array_push($result['removed'], $cmd['tag_id']);
            } else {
              array_push($result['errors'], array_merge($cmd, array('message' => '')));
            };
            break;
          default:
            array_push($result['errors'], array_merge($cmd, array('message' => 'Invalid operation')));
        }
      } catch (Exception $e) {
        array_push($result['errors'], array_merge($cmd, array('message' => $e->getMessage())));
      }
    }

    header('Content-type: application/json');
    echo json_encode($result);

    exit;
  }
};
