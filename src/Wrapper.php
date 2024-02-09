<?php

namespace Wrapper;

require __DIR__ . "/SleekDB/Store.php";

use SleekDB\Query;
use SleekDB\Store;

/**
 * @author Dardev
 * INITIALZER
 * 
 * > tout les collections sont cree avec leurs propres `configuration`.
 * 
 * ```php
 * # HERE ARE ALL CONFIGS OFERED BY THIS WRAPPER
 * 
 * [
 * "primary_key"=> "_id",
 * "auto_cache"=> true,
 * "search"=> [
 *   "min_length"=> 2, // minimum search keyword length characters
 *   "mode"=> "and", # and|or
 *   "algorithm"=> "hits", # hits|hits_prioritize|prioritize|prioritize_position
 *  ]
 * ]
 * ```
 */

class Storage
{

  /**
   * Storage
   */
  public $Storage = __DIR__ . "/../Storage";

  /**
   * @param string $storagePath le chemein ou serons concerver tout vos stores.
   * s'il n'est pas fournis le chemin par defaut est utiliser `Storage`.
   * @param object $initFunction le callback d'initialisation. recoie l'objet `this` en parametre
   * representant la classe `Storage`.
   * @return object la classe `Storage`
   */
  function __construct(string $storagePath = NULL, callable $initFunction = null)
  {
    if ($storagePath != NULL) {
      if (is_dir($storagePath)) $this->Storage = $storagePath;
      else throw new \Exception("The selected Storage are not set or unexistable.", 1);
    }

    # call init
    if (is_null($initFunction) == false) $initFunction($this);
  }

  /**
   * SELECT STORE IN STORAGE
   * 
   * 
   * @param string @Name database name
   * @param array @Configs CFG :
   * ```php
   * [
   *  "primary_key"=> "_id",
   *  "auto_cache"=> true,
   *  "search"=> [
   *    "min_kwLen"=> 2, // minimum search keyword length characters
   *    "mode"=> "and", # and|or
   *    "algo"=> "hits", # hits|hits_prioritize|prioritize|prioritize_position
   *  ],
   * ]
   * ```
   * @return _Store retourne l'objet qui offre des methods pour gerer les __Collections__ sinon __false__
   */
  public function selectStore(string $Name): _Store
  {

    # return a new Collections class *************************************************************************
    if (is_dir($this->Storage . "/" . $Name)) return new _Store($Name, $this->Storage);
    # -----------------------------------------------------------------------------------------
    else throw new \Exception("Store not set or unexistable.", 1);
  }

  # METHOMDES **********************

  /**
   * Create new Database
   * @static
   * @param string $Name The name of new database to reate. No space a permit
   * @return bool|_Store __false__ si echec et __object__ de la classe __Store__ si succes.
   */
  public function createStore(string $Name): _Store|bool
  {
    if (\strstr($Name, " ") == FALSE) {
      if (\file_exists($this->Storage . "/" . $Name) == FALSE) {
        $statut = \mkdir($this->Storage . "/" . $Name, 0777, TRUE);
        if ($statut == TRUE) return $this->selectStore($Name);
        else return FALSE; # create dir failed
      } else return FALSE; # directory exist
    } else {
      throw new \Exception("The database name must not Containe a Withe_space char.", 1);
    }
  }

  /**
   * Get a full list of Stores names
   * @return array Containing names
   */
  public function list(): array
  {
    $path = $this->Storage;
    $dirs = scandir($path);
    $list = [];
    if (count($dirs)) {
      foreach ($dirs as $path_) {
        if (is_dir($path . "/" . $path_) && $path_ != "." && $path_ != "..")
          $list[] = basename($path_);
      }
    }
    return $list;
  }

  /**
   * delete the current database
   * 
   * > cette methode peut lancer une erreur lors du renomage avec des presque sumillaire.
   * 
   * @param String $database_name the name of database to delete
   * @return bool true false if database is not find or any error occured during deletion
   */
  public function remove(string $database_name): bool
  {
    $path = $this->Storage . "/" . $database_name;
    if (empty($path)) return false;
    if (!is_dir($path)) return false;

    return $this->_remove($path);
  }

  private function _remove(string $path): bool
  {
    return is_file($path)
      ? @unlink($path)
      : array_map(
        function ($path) {
          $this->_remove($path);
        },
        glob($path . '/*')
      ) == @rmdir($path);
  }

  public function rename(string $old_name, string $new_name): bool
  {
    return rename($this->Storage . "/$old_name", $this->Storage . "/$new_name");
  }

  /**
   * Return the size of a given  Storage in Bytes (Octes)
   */
  public function Size(): int
  {
    $dir = $this->Storage;
    $size = 0;
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
      $size += $file->getSize();
    }
    return $size;
  }
}

/**
 * THIS ANONYME CLASS TO HANDLING STORES
 */
class _Store
{
  public $Storage_location = __DIR__ . "/../Storage";

  private $Configs = [
    "auto_cache" => true,
    "cache_lifetime" => null,
    "timeout" => false, #120, // deprecated! Set it to false!
    "primary_key" => "_id",
    "search" => [
      "min_length" => 2,
      "mode" => "and",
      "score_key" => "scoreKey",
      "algorithm" => Query::SEARCH_ALGORITHM["hits"] # hits, hits_prioritize, prioritize, prioritize_position
    ],
    // "folder_permissions" => 0777
  ];

  /** @var string $name current store name */
  public $Name = "";

  /**
   * Constructor
   * @access public
   * @param string Name Of the a Database to link for
   * @return Database
   * ! If database note exist. it will throw an error
   */
  function __construct(string $Name, string $Location)
  {
    // if ($cfgs == NULL) $this->Configs = $cfgs;
    $this->Name = $Name;
    $this->Storage_location = $Location;
  }

  /**
   * Gel the full list of Collections of the current Storage
   * @return array stores names list
   */
  public function list()
  {
    $path = $this->Storage_location . "/" . $this->Name;
    $dirs = scandir($path);
    $list = [];
    if (count($dirs)) {
      foreach ($dirs as $path_) {
        if (is_dir($path . "/" . $path_) && $path_ != "." && $path_ != "..")
          array_push($list, basename($path_));
      }
    }
    return $list;
  }

  /**
   * remove one Collection in the current Store
   * @return bool
   */
  public function remove(string $Collection_name): bool
  {
    $path = $this->Storage_location . '/' . $this->Name . "/" . $Collection_name;
    if (empty($path)) return false;
    if (!is_dir($path)) return false;

    return $this->_remove($path);
  }

  private function _remove(string $path): bool
  {
    return is_file($path)
      ? @unlink($path)
      : array_map(
        function ($path) {
          $this->_remove($path);
        },
        glob($path . '/*')
      ) == @rmdir($path);
  }

  /**
   * Select a collection
   * @param Store_name the store name to select for
   * @return _Collecton|null containing a list of colections to work for. any field contain a Store Object
   * all SleekDB methodes are accessible via this object
   * Ex: store->all()
   */
  public function selectCollection(string $Store): _Collecton|null
  {
    $store_list = $this->List();
    // // $cfgs = $this->Configs;
    $obj = null;
    foreach ($store_list as $Store_name) {
      if ($Store_name == $Store) {
        $dbPath = $this->Storage_location . "/" . $this->Name;
        $cfgs   = file_get_contents($dbPath . "/$Store/config.json");
        if (is_string($cfgs))
          $obj = new _Collecton($Store_name, $dbPath, json_decode($cfgs, true));
        else throw new \Exception("Confilg file not found in " . $Store, 1);
        break;
      }
    }
    return $obj;
  }

  /**
   * get all collections
   * @return array containing a list of colections to work for. any field contain a Store Object
   * all SleekDB methodes are accessible via this object
   * Ex: db["users"]->getAll()
   */
  public function all(): array
  {
    $store_list = $this->list();
    // // $cfgs = $this->Configs;
    $objs = [];
    foreach ($store_list as $Store_name) {
      $dbPath = $this->Storage_location . "/" . $this->Name;
      // // $objs[$Store_name] = $this->Fields($Store_name, $dbPath, $cfgs);
      $cfgs   = file_get_contents($dbPath . "/$Store_name/config.json");
      if (is_string($cfgs))
        $objs[$Store_name] = new _Collecton($Store_name, $dbPath, json_decode($cfgs, true));
      else throw new \Exception("Confilg file not found in " . $Store_name, 1);
    }
    return $objs;
  }

  /**
   * Renama a collection
   * @param string $New_name name the new name to give to the current collection
   * @return bool
   */
  public function rename(string $old_name, string $new_name): bool
  {
    $old_name = $this->Storage_location . '/' . $this->Name . "/" . $old_name;
    $new_name = $this->Storage_location . '/' . $this->Name . "/" . $new_name;

    if (is_dir($new_name) == FALSE) {
      if (rename($old_name, $new_name)) return TRUE;
      else return FALSE;
    } else return FALSE;
  }

  /**
   * Create new Collection in the current Store
   * @param string $Name - name of new collection to create in store.
   * @param string $Configs Wrapper Configs.
   * 
   * ```php
   * # HERE ARE ALL CONFIGS OFERED BY THIS WRAPPER
   * 
   * [
   * "primary_key"=> "_id",
   * "auto_cache"=> true,
   * "search"=> [
   *   "min_length"=> 2, // minimum search keyword length characters
   *   "mode"=> "and", # and|or
   *   "algorithm"=> "hits", # hits|hits_prioritize|prioritize|prioritize_position
   *  ]
   * ]
   * ```
   * 
   * @return _Collecton|null|bool return the created collection object otherwise False.
   * 
   * > _This methode will create config.json file in the store fold_
   * 
   * ? The index seted in config proprety will be add automaticaly to any value. add them here will have no effect
   * * dont forget tha all the Parent Methodes are accessible.
   * * some Parent methodes are overiden
   */
  public function createCollection(string $Name, array $Configs = null): _Collecton|null|bool
  {
    # control configs
    if (is_null($Configs)) $Configs = $this->Configs;
    if ($Configs != NULL && is_array($Configs)) {
      if (isset($Configs["primary_key"])) $this->Configs["primary_key"] = $Configs["primary_key"];
      if (isset($Configs["auto_cache"])) $this->Configs["auto_cache"] = $Configs["auto_cache"];
      if (isset($Configs["search"])) {
        if (isset($Configs["search"]["min_kwLen"]) && is_int($Configs["search"]["min_kwLen"])) $this->Configs["search"]["min_length"] = $Configs["search"]["min_length"];
        if (isset($Configs["search"]["mode"]) && is_string($Configs["search"]["mode"])) $this->Configs["search"]["mode"] = $Configs["search"]["mode"];
        if (isset($Configs["search"]["algorithm"])) {
          $Algorithm = Query::SEARCH_ALGORITHM["hits"];
          switch ($Configs["search"]["algorithm"]) {
            case 'hits_prioritize' || 2:
              $Algorithm = Query::SEARCH_ALGORITHM["hits_prioritize"];
              break;
            case 'prioritize' || 3:
              $Algorithm = Query::SEARCH_ALGORITHM["prioritize"];
              break;
            case 'prioritize_position' || 4:
              $Algorithm = Query::SEARCH_ALGORITHM["prioritize_position"];
              break;

            default:
              $Algorithm = Query::SEARCH_ALGORITHM["hits"];
              break;
          }
          $this->Configs["search"]["algorithm"] = $Algorithm;
        }
      }
    }

    # creating collection
    if (\strstr($Name, " ") == FALSE) {
      $path = $this->Storage_location . "/" . $this->Name;
      new Store($Name, $path, $this->Configs);

      # create Config file
      file_put_contents($path . "/$Name/config.json", json_encode($this->Configs));
      return $this->selectCollection($Name);
    } else {
      throw new \Exception("No white character are permited : space not permited on a name.", 1);
      return FALSE;
    }
  }

  /**
   * get configuration data of a collection
   */
  public function getConfigOf(string $Collection_name): array
  {
    $path = $this->Storage_location . "/" . $this->Name;
    $file = $path . "/$Collection_name/config.json";

    if (is_file($file)) {
      $file = file_get_contents($file);
      return json_decode($file, true);
    } else return [];
  }

  /**
   * set configuration data of a collection if has config file.
   */
  public function setConfigOf(string $Collection_name, array $Configs): bool
  {
    $path = $this->Storage_location . "/" . $this->Name;
    $file_path = $path . "/$Collection_name/config.json";

    if (is_file($file_path)) {
      $file = file_get_contents($file_path);
      $default = json_decode($file, true);

      if ($Configs != NULL && is_array($Configs)) {
        if (isset($Configs["primary_key"])) $default["primary_key"] = $Configs["primary_key"];
        if (isset($Configs["auto_cache"])) $default["auto_cache"] = $Configs["auto_cache"];
        if (isset($Configs["search"])) {
          if (isset($Configs["search"]["min_kwLen"]) && is_int($Configs["search"]["min_kwLen"])) $default["search"]["min_length"] = $Configs["search"]["min_length"];
          if (isset($Configs["search"]["mode"]) && is_string($Configs["search"]["mode"])) $default["search"]["mode"] = $Configs["search"]["mode"];
          if (isset($Configs["search"]["algorithm"])) {
            $Algorithm = Query::SEARCH_ALGORITHM["hits"];
            switch ($Configs["search"]["algorithm"]) {
              case 'hits_prioritize' || 2:
                $Algorithm = Query::SEARCH_ALGORITHM["hits_prioritize"];
                break;
              case 'prioritize' || 3:
                $Algorithm = Query::SEARCH_ALGORITHM["prioritize"];
                break;
              case 'prioritize_position' || 4:
                $Algorithm = Query::SEARCH_ALGORITHM["prioritize_position"];
                break;

              default:
                $Algorithm = Query::SEARCH_ALGORITHM["hits"];
                break;
            }
            $default["search"]["algorithm"] = $Algorithm;
          }
        }
      }

      file_put_contents($file_path, json_encode($default));
      return true;
    } else return false;
  }

  /**
   * Return the size of a given collection in Byte (Octes)
   */
  public function size(string $name): int
  {
    $dir = $this->Storage_location . "/" . $this->Name . '/' . $name;

    $size = 0;
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
      $size += $file->getSize();
    }
    return $size;
  }

  /**
   * Return the size of this store in Byte (Octes)
   */
  public function storeSize(): int
  {
    $dir = $this->Storage_location . "/" . $this->Name;

    $size = 0;
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
      $size += $file->getSize();
    }
    return $size;
  }

  # FILDS ************************************************************************************
  /**
   * GENERATE A STORE OBJECT FOR A SPECIFIC STORE
   * ? OVERLOAD ALL OR SOME MOTHER METHODES
   */
  private function Fields($storeName, $databasePath, $Configs)
  {

    /**
     * THIS ANONYME CLASS IS A WRAPPER OF SLEEKDB CLASS
     */
    return new class($storeName, $databasePath, $Configs) extends Store
    {
      /**
       * CONSTRUCTOR
       */
      function __construct($storeName, $databasePath, $Configs)
      {

        parent::__construct($storeName, $databasePath, $Configs);

        $this->$storeName = $storeName;
        $this->$databasePath = $databasePath;
      }

      public $storeName;
      public $databasePath;

      /**
       * A WRAPPER OF INSERT & INSERTMANY
       * @return array can return FALSE if something else.
       */
      public function add(array $Data)
      {
        if (count($Data)) {
          if (isset($Data[0])) {
            if (is_array($Data[0])) return parent::insertMany($Data);
            else return parent::insert($Data);
          } else return parent::insert($Data);
        } else return FALSE;
      }

      /**
       * A WRAPPER OF findOnBy
       * @return array can return NULL if something else.
       */
      public function get(array $criteria)
      {
        return parent::findOneBy($criteria);
      }

      /**
       * A WRAPPER OF UPDATE
       */
      public function edit(array $updatable)
      {
        return parent::update($updatable);
      }

      /**
       * A WRAPPER OF DELETEBY
       */
      public function delete(array $criteria)
      {
        return parent::deleteBy($criteria);
      }

      /**
       * A WRAPPER OF findAll
       */
      public function getAll(array $orderBy = null, int $limit = null, int $offset = null)
      {
        return parent::findAll($orderBy, $limit, $offset);
      }

      /**
       * Criterias simple and clear
       * ```php
       * ["key : operator"=> object]
       * ```
       */
      public static function criteria(array $criteria): array
      {
        // todo implemente here ...
        return $criteria;
      }

      /**
       * Return the size of the given field index in Byte (Octes)
       */
      public function size(int $index): int
      {
        $size = filesize($this->databasePath . '/' . $this->storeName . "/data/$index.json");
        if ($size === false) return 0;
        else return $size;
      }

      # Chemat validator
      /**
       * validate a data.
       * check if the chem is set to Store. before verify. if not set return true and nothing is do
       * @param Data
       * @return bool
       */
      private function ValidateShemat(array $Data)
      {
      }
    };
  }
  # END FIELD --------------------------------------------------------------------------------
}

/**
 * THIS ANONYME CLASS IS A WRAPPER OF SLEEKDB CLASS
 */
class _Collecton extends Store
{
  /**
   * CONSTRUCTOR
   */
  function __construct($storeName, $databasePath, $Configs)
  {

    parent::__construct($storeName, $databasePath, $Configs);

    $this->$storeName = $storeName;
    $this->$databasePath = $databasePath;
  }

  public $storeName;
  public $databasePath;

  /**
   * A WRAPPER OF INSERT & INSERTMANY
   * @return array can return FALSE if something else.
   */
  public function add(array $Data)
  {
    if (count($Data)) {
      if (isset($Data[0])) {
        if (is_array($Data[0])) return parent::insertMany($Data);
        else return parent::insert($Data);
      } else return parent::insert($Data);
    } else return FALSE;
  }

  /**
   * A WRAPPER OF findOnBy
   * @return array can return NULL if something else.
   */
  public function get(array $criteria)
  {
    return parent::findOneBy($criteria);
  }

  /**
   * A WRAPPER OF UPDATE
   */
  public function edit(array $updatable)
  {
    return parent::update($updatable);
  }

  /**
   * A WRAPPER OF DELETEBY
   */
  public function delete(array $criteria)
  {
    return parent::deleteBy($criteria);
  }

  /**
   * A WRAPPER OF findAll
   */
  public function getAll(array $orderBy = null, int $limit = null, int $offset = null)
  {
    return parent::findAll($orderBy, $limit, $offset);
  }

  /**
   * Criterias simple and clear
   * ```php
   * ["key : operator"=> object]
   * ```
   */
  public static function criteria(array $criteria): array
  {
    // todo implemente here ...
    return $criteria;
  }

  /**
   * Return the size of the given field index in Byte (Octes)
   */
  public function size(int $index): int
  {
    $size = filesize($this->databasePath . '/' . $this->storeName . "/data/$index.json");
    if ($size === false) return 0;
    else return $size;
  }

  # Chemat validator
  /**
   * validate a data.
   * check if the chem is set to Store. before verify. if not set return true and nothing is do
   * @param Data
   * @return bool
   */
  private function ValidateShemat(array $Data)
  {
  }
}
