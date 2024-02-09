<?php
require __DIR__ . "/../../src/Wrapper.php";
include __DIR__ . "/storage_paths.php";

use Wrapper\Storage;

class Stores {
  private $storage = null;
  function __construct() {
    global $STORAGE_PATH;
    $this->storage = new Storage($STORAGE_PATH);
  }
  function list(): array {
    return $this->storage->list();
  }

  function remove($store_name): bool {
    return $this->storage->remove($store_name);
  }

  function rename($old, $new): bool {
    return $this->storage->rename($old, new_name: $new);
  }

  function create($name): bool {
    $return = $this->storage->createStore($name);
    if ($return === false) return FALSE;
    else return TRUE;
  }

  function size() {
    return $this->storage->size();
  }
}

// $s = new Stores();
// print_r($s->list());
// var_dump($s->size());

class Colletions {
  private $name = "";
  private $store_name = "";
  private $storage = null;

  function __construct($store_name, $col_name) {
    global $STORAGE_PATH;
    $this->name = $col_name;
    $this->store_name = $store_name;
    $this->storage = new Storage($STORAGE_PATH);
  }

  function list(): array {
    $col = $this->storage->selectStore($this->store_name);
    return $col->list();
  }

  function rename($new_name): bool {
    $col = $this->storage->selectStore($this->store_name);
    return $col->rename($this->name, $new_name);
  }

  function create(string $name, array $config): bool {
    $col = $this->storage->selectStore($this->store_name);
    $state = $col->createCollection($name, $config);
    if ($state === false) return true;
    else return false;
  }

  function remove(): bool {
    $col = $this->storage->selectStore($this->store_name);
    return $col->remove($this->name);
  }

  function get_configs(): array {
    $col = $this->storage->selectStore($this->store_name);
    return $col->getConfigOf($this->name);
  }

  function update_configs(array $data): bool {
    $col = $this->storage->selectStore($this->store_name);
    return $col->setConfigOf($this->name, $data);
    return false;
  }

  function size() {
    $col = $this->storage->selectStore($this->store_name);
    return $col->size($this->name);
  }

  function storeSize() {
    $col = $this->storage->selectStore($this->store_name);
    return $col->storeSize();
  }
}

// $s = new Colletions("test", 'datass');
// print_r($s->list());
// var_dump($s->rename('datass'));
// var_dump($s->get_configs());
// var_dump($s->update_configs(['auto_cache'=> true]));
// var_dump($s->size());
// var_dump($s->storeSize());

class Fields {
  private $store_name = "";
  private $collection_name = "";
  private $storage = null;

  function __construct($store_name, $collection_name) {
    global $STORAGE_PATH;
    $this->store_name = $store_name;
    $this->collection_name = $collection_name;
    $this->storage = new Storage($STORAGE_PATH);
  }
  
  function find($name): array {
    $col = $this->storage->selectStore($this->store_name);
    $path = $col->Storage_location . '/' . $col->Name . "/" . $this->collection_name . '/data';

    foreach (glob($path . '*/*') as $element) {
      if (basename($element, '.json') == $name) {
        return json_decode( file_get_contents($element) , true);
      }
    }
    return [];
  }

  function list($at=1): array {
    $col = $this->storage->selectStore($this->store_name);
    $path = $col->Storage_location . '/' . $col->Name . "/" . $this->collection_name . '/data';

    $list = [];
    foreach (glob($path . '*/*') as $element) {
      $list[] = basename($element, '.json');
    }
    $to = intdiv(count($list), 50);
    $to = $to <= 0 ? 1 : $to;
    $at = ($at - 1) <= 0 ? 0 : intval($at);
    $list = array_slice($list, $at * 50, 50, true);
    sort($list, SORT_NUMERIC);
    return [
      'list' => $list,
      'total' => count($list),
      'at'=> $at <= 0 ? 1 : $at,
      'of'=> $to,
    ];
  }

}

// $s = new Fields('test', 'datass');
// print_r($s->find('2'));
// print_r($s->list());

class FieldDatas {
  private $store_name = "";
  private $collection_name = "";
  private $storage = null;

  function __construct($store_name, $collection_name) {
    global $STORAGE_PATH;
    $this->store_name = $store_name;
    $this->collection_name = $collection_name;
    $this->storage = new Storage($STORAGE_PATH);
  }

  function get(int $index): array {
    $col = $this->storage->selectStore($this->store_name);
    $field = $col->selectCollection($this->collection_name);
    $confg = $col->getConfigOf($this->collection_name);
    $state = $field->get([$confg['primary_key'], '=', $index]);
    return $state;
  }

  function add($data): array {
    $col = $this->storage->selectStore($this->store_name);
    $field = $col->selectCollection($this->collection_name);
    $state = $field->add($data);
    return $state;
  }

  function remove(int $index): bool {
    $col = $this->storage->selectStore($this->store_name);
    $field = $col->selectCollection($this->collection_name);
    $confg = $col->getConfigOf($this->collection_name);
    $state = $field->delete([$confg['primary_key'], '=', $index]);
    return $state;
  }

  function update(int $index, array $data): bool {
    $col = $this->storage->selectStore($this->store_name);
    $field = $col->selectCollection($this->collection_name);
    $confg = $col->getConfigOf($this->collection_name);
    $data[$confg['primary_key']] = $index;
    $state = $field->edit($data);
    return $state;
  }

  function size(int $index): int {
    $col = $this->storage->selectStore($this->store_name);
    $field = $col->selectCollection($this->collection_name);
    $size = $field->size($index);
    return $size;
  }
}

// $s = new FieldDatas('test', 'datass');
// print_r($s->add(['age'=> 12, 'name'=> "raissa"]));
// var_dump($s->remove(5));
// var_dump($s->update(4, ['book'=> 'long life']));
// var_dump($s->size(4));