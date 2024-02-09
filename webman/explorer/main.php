<?php
include __DIR__ . "/explorer.php";

/*
panel
action
*/

header('Content-Type: application/json',true, 200);

if ($_GET['panel'] == 'stores') {
  $s = new Stores();
  switch ($_GET['action']) {
    case 'list': {
      print json_encode($s->list());
    } break;
    case 'size': {
      print $s->size();
    } break;
    case 'create': {
      print intval($s->create($_GET['name']));
    } break;
    case 'rename': {
      print intval($s->rename($_GET['old'], $_GET['new']));
    } break;
    case 'remove': {
      print intval($s->remove($_GET['name']));
    } break;
    case 'get_path_names': {
      $list = [];
      foreach (STORAGES_PATHS as $key => $value) {
        $list[] = $key;
      }
      print json_encode($list);
    } break;
    case 'get_path_name': {
      $state = file_get_contents('./storage_paths.txt');
      print $state;
    } break;
    case 'set_path': {
      $state = file_put_contents('./storage_paths.txt', $_GET['name']);
      if ($state) print 1;
      else print 0;
    } break;
    
    default: {} break;
  }

} elseif ($_GET['panel'] == 'collections') {
  $s = new Colletions($_GET['store'], $_GET['collection']);
  switch ($_GET['action']) {
    case 'list': {
      print json_encode($s->list());
    } break;
    case 'rename': {
      print intval($s->rename($_GET['name']));
    } break;
    case 'create': {
      print intval($s->create($_GET['name'], json_decode($_GET['config'], true)));
    } break;
    case 'get_config': {
      print json_encode($s->get_configs());
    } break;
    case 'update_config': {
      print intval($s->update_configs(json_decode($_GET['config'], true)));
    } break;
    case 'remove': {
      print intval($s->remove());
    } break;
    case 'size': {
      print $s->size();
    } break;
    case 'store_size': {
      print $s->storeSize();
    } break;
    
    default: {} break;
  }

} elseif ($_GET['panel'] == 'fields') {
  $s = new Fields($_GET['store'], $_GET['collection']);
  switch ($_GET['action']) {
    case 'find': {
      print json_encode($s->find($_GET['name']));
    } break;
    case 'list': {
      print json_encode($s->list($_GET['at']));
    } break;
    
    default: {} break;
  }

} elseif ($_GET['panel'] == 'data') {
  $s = new FieldDatas($_GET['store'], $_GET['collection']);
  switch ($_GET['action']) {
    case 'add': {
      print json_encode($s->add(json_decode($_GET['data'], true)));
    } break;
    case 'remove': {
      print intval($s->remove(intval($_GET['index'])));
    } break;
    case 'update': {
      print intval($s->update(intval($_GET['index']), json_decode($_GET['data'] , true)));
    } break;
    case 'get': {
      print json_encode($s->get(intval($_GET['index'])));
    } break;
    case 'size': {
      print $s->size(intval($_GET['index']));
    } break;
    
    default: {} break;
  }
}
