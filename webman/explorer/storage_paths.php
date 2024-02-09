<?php
/**
 * `STORAGES_PATHS` vous permet d'avoir une large file des repertoires de stokage
 * que l'application utilisera pour permetre via l'interface de contol de basculer entre 
 * plusieurs Stockages.
 * 
 * le fichier   storage_paths.txt` contient que le nom du Stokage qui est selectionner.
 * _ce fiehier ne doit contenir aucun caractere de nouvelle ligne ni des espaces._
 * 
 */
const STORAGES_PATHS = [
  "default"=> __DIR__ . "/../../Storage",
  # You can add any others Paths you want here...
];

$STORAGE_PATH = STORAGES_PATHS[trim(file_get_contents('./storage_paths.txt'))];