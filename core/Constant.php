<?php

namespace VietnixImportCsv\Core;

class Constant
{
    //Plugin basic Settings
    const PLUGIN = [
        'NAME' => 'Vietnix Import CSV Plugin',
        'SLUG' => 'vietnix-import-csv-plugin',
        'VERSION' => '1.0.3',
        'OPTIONS_PREFIX' => 'vietnix_import_csv_',
    ];
    // Plugin Paths
    private static $PATHS = [
        'DIR' => null,
        'URL' => null,
        'PATH' => null,
        'ASSETS_URL' => null,
        'ASSETS_PATH' => null,
        'IMAGES_URL' => null,
    ];
    //Plugin path
    public static function init()
    {
        // Lấy đường dẫn từ file plugin chính, không phải file Constant.php
        $plugin_file = dirname(__DIR__) . '/vietnix-csv-import.php';

        self::$PATHS['DIR'] = dirname($plugin_file);
        self::$PATHS['URL'] = plugin_dir_url($plugin_file);
        self::$PATHS['PATH'] = plugin_dir_path($plugin_file);
        self::$PATHS['BASENAME'] = plugin_basename($plugin_file);
    }
    // Define Constants
    public static function define_Constants()
    {
        self::init();

        define('VIETNIX_IMPORT_CSV_PLUGIN_NAME', self::PLUGIN['NAME']);
        define('VIETNIX_IMPORT_CSV_PLUGIN_SLUG', self::PLUGIN['SLUG']);
        define('VIETNIX_IMPORT_CSV_PLUGIN_VERSION', self::PLUGIN['VERSION']);
        define('VIETNIX_IMPORT_CSV_PLUGIN_OPTIONS_PREFIX', self::PLUGIN['OPTIONS_PREFIX']);


        define('VIETNIX_IMPORT_CSV_PLUGIN_DIR', self::$PATHS['DIR']);
        define('VIETNIX_IMPORT_CSV_PLUGIN_URL', self::$PATHS['URL']);
        define('VIETNIX_IMPORT_CSV_PLUGIN_PATH', self::$PATHS['PATH']);
        define('VIETNIX_IMPORT_CSV_PLUGIN_BASENAME', self::$PATHS['BASENAME']);


    }
}