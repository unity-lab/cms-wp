<?php
declare(strict_types = 1);
namespace Automattic\WooCommerce\EmailEditor\Engine\PersonalizationTags;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Engine\Logger\Email_Editor_Logger;
class Personalization_Tags_Registry {
 private Email_Editor_Logger $logger;
 private $tags = array();
 public function __construct( Email_Editor_Logger $logger ) {
 $this->logger = $logger;
 }
 public function initialize(): void {
 $this->logger->info( 'Initializing personalization tags registry' );
 apply_filters( 'woocommerce_email_editor_register_personalization_tags', $this );
 $this->logger->info( 'Personalization tags registry initialized', array( 'tags_count' => count( $this->tags ) ) );
 }
 public function register( Personalization_Tag $tag ): void {
 if ( isset( $this->tags[ $tag->get_token() ] ) ) {
 $this->logger->warning(
 'Personalization tag already registered',
 array(
 'token' => $tag->get_token(),
 'name' => $tag->get_name(),
 'category' => $tag->get_category(),
 )
 );
 return;
 }
 $this->tags[ $tag->get_token() ] = $tag;
 $this->logger->debug(
 'Personalization tag registered',
 array(
 'token' => $tag->get_token(),
 'name' => $tag->get_name(),
 'category' => $tag->get_category(),
 )
 );
 }
 public function get_by_token( string $token ): ?Personalization_Tag {
 return $this->tags[ $token ] ?? null;
 }
 public function get_all() {
 return $this->tags;
 }
}
