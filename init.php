<?php

namespace MyStore;

require(__DIR__ . "/src/Wrapper.php");

use Wrapper\_Store;
use Wrapper\Storage;

/**
 * expose only store() in this namespace
 */
function DB(): _Store
{

  /**
   * INITIALIZE DATABASE
   * @param $databasePath default is NULL.
   * @return Initalizer
   * if databasePath is leave NULL the defautl Database path is used.
   * dafault path : ./Storage
   * if you want to set a new default path. user the absolute path insted of relative
   * Ex: __DIR__ . "/Storage"
   */
  $STORAGE = new Storage(null, function ($storage) {
    $store = $storage->createStore("test2");
    if ($store !== FALSE) {
      $collection = $store->createCollection('datas', []);
      $collection->add(['name' => 'princei eugene']);

      $store->createCollection('data2', []);
      $store->createCollection('data3', []);
    }
  });

  return $STORAGE->selectStore("test2");
}

// ! -------------------------------------------------------------------------------- ! //

/**
 * SET A DEFAULT DATABASE
 */
// $DATABASE = $STORAGE->Select(
//   "Database_1", [ // SELECTED DB

//   /** HERE ARE ALL CONFIGS OFERED BY THIS WRAPPER */
//   "primary_key"=> "_id",
//   "auto_cache"=> true,
//   "search"=> [
//     "min_kwLen"=> 2, // minimum search keyword length characters
//     "mode"=> "and", # and|or
//     "algo"=> "hits", # hits|hits_prioritize|prioritize|prioritize_position
//   ]
// ]);

# create Store
// $STORAGE->Create("test2");

# list 
// print_r( $STORAGE->list() );

# size
// // print_r( $STORAGE->Size('test') );

# rename 
// print_r( $STORAGE->rename('testo', 'tests') );

# remove
// print_r( $STORAGE->Remove('test2') );

# select Store
// $s = $STORAGE->Store('test');

# create Collection in store
// $s->create('datas', ['primary_key'=> 'db_index']);

# selecting collection
// $c = $s->collection('datas');

# get collections list
// print_r( $s->list() );

# select all collections
// $c = $s->all();

# get a collection size
// // print_r( $s->size('datas') );

# rename a collection
// print_r( $s->rename('dato', 'datas') );

# remove collection
// var_dump( $s->remove('datas') );

# test add data in field
// $c->add(['book'=> 'life', 'author'=> 'prince']);
