<?php
if (!defined('ABSPATH')) exit;
 // phpcs:ignore
class Latest_Autoloader_Guard {
 private $plugins_handler;
 private $autoloader_handler;
 private $autoloader_locator;
 public function __construct( $plugins_handler, $autoloader_handler, $autoloader_locator ) {
 $this->plugins_handler = $plugins_handler;
 $this->autoloader_handler = $autoloader_handler;
 $this->autoloader_locator = $autoloader_locator;
 }
 public function should_stop_init( $current_plugin, $plugins, $was_included_by_autoloader ) {
 global $jetpack_autoloader_latest_version;
 // We need to reset the autoloader when the plugins change because
 // that means the autoloader was generated with a different list.
 if ( $this->plugins_handler->have_plugins_changed( $plugins ) ) {
 $this->autoloader_handler->reset_autoloader();
 }
 // When the latest autoloader has already been found we don't need to search for it again.
 // We should take care however because this will also trigger if the autoloader has been
 // included by an older one.
 if ( isset( $jetpack_autoloader_latest_version ) && ! $was_included_by_autoloader ) {
 return true;
 }
 $latest_plugin = $this->autoloader_locator->find_latest_autoloader( $plugins, $jetpack_autoloader_latest_version );
 if ( isset( $latest_plugin ) && $latest_plugin !== $current_plugin ) {
 require $this->autoloader_locator->get_autoloader_path( $latest_plugin );
 return true;
 }
 return false;
 }
 public function check_for_conflicting_autoloaders() {
 if ( ! defined( 'JETPACK_AUTOLOAD_DEBUG_CONFLICTING_LOADERS' ) || ! JETPACK_AUTOLOAD_DEBUG_CONFLICTING_LOADERS ) {
 return;
 }
 global $jetpack_autoloader_loader;
 if ( ! isset( $jetpack_autoloader_loader ) ) {
 return;
 }
 $prefixes = array();
 foreach ( ( $jetpack_autoloader_loader->get_class_map() ?? array() ) as $classname => $data ) {
 $parts = explode( '\\', trim( $classname, '\\' ) );
 array_pop( $parts );
 while ( $parts ) {
 $prefixes[ implode( '\\', $parts ) . '\\' ] = true;
 array_pop( $parts );
 }
 }
 foreach ( ( $jetpack_autoloader_loader->get_psr4_map() ?? array() ) as $prefix => $data ) {
 $parts = explode( '\\', trim( $prefix, '\\' ) );
 while ( $parts ) {
 $prefixes[ implode( '\\', $parts ) . '\\' ] = true;
 array_pop( $parts );
 }
 }
 $autoload_chain = spl_autoload_functions();
 if ( ! $autoload_chain ) {
 return;
 }
 foreach ( $autoload_chain as $autoloader ) {
 // No need to check anything after us.
 if ( is_array( $autoloader ) && is_string( $autoloader[0] ) && substr( $autoloader[0], 0, strlen( __NAMESPACE__ ) + 1 ) === __NAMESPACE__ . '\\' ) {
 break;
 }
 // We can check Composer autoloaders easily enough.
 if ( is_array( $autoloader ) && $autoloader[0] instanceof \Composer\Autoload\ClassLoader && is_callable( array( $autoloader[0], 'getPrefixesPsr4' ) ) ) {
 $composer_autoloader = $autoloader[0];
 foreach ( $composer_autoloader->getClassMap() as $classname => $path ) {
 if ( $jetpack_autoloader_loader->find_class_file( $classname ) ) {
 $msg = "A Composer autoloader is registered with a higher priority than the Jetpack Autoloader and would also handle some of the classes we handle (e.g. $classname => $path). This may cause strange and confusing problems.";
 wp_trigger_error( '', $msg );
 continue 2;
 }
 }
 foreach ( $composer_autoloader->getPrefixesPsr4() as $prefix => $paths ) {
 if ( isset( $prefixes[ $prefix ] ) ) {
 $path = array_pop( $paths );
 $msg = "A Composer autoloader is registered with a higher priority than the Jetpack Autoloader and would also handle some of the namespaces we handle (e.g. $prefix => $path). This may cause strange and confusing problems.";
 wp_trigger_error( '', $msg );
 continue 2;
 }
 }
 foreach ( $composer_autoloader->getPrefixes() as $prefix => $paths ) {
 if ( isset( $prefixes[ $prefix ] ) ) {
 $path = array_pop( $paths );
 $msg = "A Composer autoloader is registered with a higher priority than the Jetpack Autoloader and would also handle some of the namespaces we handle (e.g. $prefix => $path). This may cause strange and confusing problems.";
 wp_trigger_error( '', $msg );
 continue 2;
 }
 }
 }
 }
 }
}
