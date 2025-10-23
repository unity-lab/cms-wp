<?php
/**
 * WordPress MCP Prompt class for representing MCP prompts according to the specification.
 *
 * @package McpAdapter
 */

declare( strict_types=1 );

namespace WP\MCP\Domain\Prompts;

use WP\MCP\Core\McpServer;
use WP_Ability;

/**
 * Represents an MCP prompt according to the Model Context Protocol specification.
 *
 * Prompts provide structured messages and instructions for interacting with language models.
 * Each prompt is uniquely identified by a name and can include arguments for customization.
 *
 * @link https://modelcontextprotocol.io/specification/2025-06-18/server/prompts
 */
class McpPrompt {

	/**
	 * The ability name.
	 *
	 * @var string
	 */
	private string $ability;

	/**
	 * Unique identifier for the prompt.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Optional human-readable name of the prompt for display purposes.
	 *
	 * @var string|null
	 */
	private ?string $title;

	/**
	 * Optional human-readable description of the prompt.
	 *
	 * @var string|null
	 */
	private ?string $description;

	/**
	 * Optional list of arguments for prompt customization.
	 *
	 * @var array
	 */
	private array $arguments;

	/**
	 * The MCP server instance this prompt belongs to.
	 *
	 * @var \WP\MCP\Core\McpServer
	 */
	private McpServer $mcp_server;

	/**
	 * Constructor for McpPrompt.
	 *
	 * @param string      $ability The ability name.
	 * @param string      $name Unique identifier for the prompt.
	 * @param string|null $title Optional human-readable name for display.
	 * @param string|null $description Optional human-readable description.
	 * @param array       $arguments Optional list of arguments for customization.
	 */
	public function __construct(
		string $ability,
		string $name,
		?string $title = null,
		?string $description = null,
		array $arguments = array()
	) {
		$this->ability     = $ability;
		$this->name        = $name;
		$this->title       = $title;
		$this->description = $description;
		$this->arguments   = $arguments;
	}

	/**
	 * Get the prompt name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the prompt title.
	 *
	 * @return string|null
	 */
	public function get_title(): ?string {
		return $this->title;
	}

	/**
	 * Get the prompt description.
	 *
	 * @return string|null
	 */
	public function get_description(): ?string {
		return $this->description;
	}

	/**
	 * Get the prompt arguments.
	 *
	 * @return array
	 */
	public function get_arguments(): array {
		return $this->arguments;
	}

	/**
	 * Get the ability name.
	 *
	 * @return \WP_Ability|null
	 */
	public function get_ability(): ?WP_Ability {
		return wp_get_ability( $this->ability );
	}

	/**
	 * Set the prompt title.
	 *
	 * @param string|null $title The title to set.
	 *
	 * @return void
	 */
	public function set_title( ?string $title ): void {
		$this->title = $title;
	}

	/**
	 * Set the prompt description.
	 *
	 * @param string|null $description The description to set.
	 *
	 * @return void
	 */
	public function set_description( ?string $description ): void {
		$this->description = $description;
	}

	/**
	 * Set the prompt arguments.
	 *
	 * @param array $arguments The arguments to set.
	 *
	 * @return void
	 */
	public function set_arguments( array $arguments ): void {
		$this->arguments = $arguments;
	}

	/**
	 * Add an argument to the prompt.
	 *
	 * @param array $argument The argument to add.
	 *
	 * @return void
	 */
	public function add_argument( array $argument ): void {
		$this->arguments[] = $argument;
	}

	/**
	 * Remove an argument by name.
	 *
	 * @param string $name The argument name to remove.
	 *
	 * @return void
	 */
	public function remove_argument( string $name ): void {
		$this->arguments = array_filter(
			$this->arguments,
			static function ( $argument ) use ( $name ) {
				return ( $argument['name'] ?? '' ) !== $name;
			}
		);
		// Re-index array.
		$this->arguments = array_values( $this->arguments );
	}

	/**
	 * Get an argument by name.
	 *
	 * @param string $name The argument name.
	 *
	 * @return array|null The argument if found, null otherwise.
	 */
	public function get_argument( string $name ): ?array {
		foreach ( $this->arguments as $argument ) {
			if ( ( $argument['name'] ?? '' ) === $name ) {
				return $argument;
			}
		}

		return null;
	}

	/**
	 * Check if an argument exists.
	 *
	 * @param string $name The argument name.
	 *
	 * @return bool True if argument exists, false otherwise.
	 */
	public function has_argument( string $name ): bool {
		return $this->get_argument( $name ) !== null;
	}

	/**
	 * Convert the prompt to an array representation according to MCP specification.
	 *
	 * @return array
	 */
	public function to_array(): array {
		$prompt_data = array(
			'name' => $this->name,
		);

		// Add optional fields only if they have values.
		if ( ! is_null( $this->title ) ) {
			$prompt_data['title'] = $this->title;
		}

		if ( ! is_null( $this->description ) ) {
			$prompt_data['description'] = $this->description;
		}

		if ( ! empty( $this->arguments ) ) {
			$prompt_data['arguments'] = $this->arguments;
		}

		return $prompt_data;
	}

	/**
	 * Convert the prompt to JSON representation.
	 *
	 * @return string
	 */
	public function to_json(): string {
		return wp_json_encode( $this->to_array() );
	}

	/**
	 * Create an McpPrompt instance from an array.
	 *
	 * @param array     $data Array containing prompt data.
	 * @param \WP\MCP\Core\McpServer $mcp_server The MCP server instance.
	 *
	 * @return \WP\MCP\Domain\Prompts\McpPrompt Returns a new McpPrompt instance.
	 * @throws \InvalidArgumentException If required fields are missing or validation fails.
	 */
	public static function from_array( array $data, McpServer $mcp_server ): self {
		$prompt = new self(
			$data['ability'] ?? '',
			$data['name'] ?? '',
			$data['title'] ?? null,
			$data['description'] ?? null,
			$data['arguments'] ?? array()
		);

		$prompt->set_mcp_server( $mcp_server );

		return $prompt->validate( "McpPrompt::from_array::{$data['name']}" );
	}

	/**
	 * Validate the prompt instance.
	 *
	 * @param string $context Optional context for error messages.
	 *
	 * @return self Returns the validated tool instance.
	 * @throws \InvalidArgumentException If validation fails.
	 */
	public function validate( string $context = '' ): self {
		if ( ! $this->mcp_server->is_mcp_validation_enabled() ) {
			return $this;
		}

		$context_to_use = $context ?: "McpPrompt::{$this->name}";
		McpPromptValidator::validate_prompt_instance( $this, $context_to_use );

		return $this;
	}

	/**
	 * Create a standard argument definition.
	 *
	 * @param string      $name The argument name.
	 * @param string|null $description Optional argument description.
	 * @param bool        $required Whether the argument is required.
	 *
	 * @return array The argument definition.
	 */
	public static function create_argument( string $name, ?string $description = null, bool $required = false ): array {
		$argument = array(
			'name' => $name,
		);

		if ( ! is_null( $description ) ) {
			$argument['description'] = $description;
		}

		if ( $required ) {
			$argument['required'] = true;
		}

		return $argument;
	}

	/**
	 * Get the MCP server instance this tool belongs to.
	 *
	 * @return \WP\MCP\Core\McpServer
	 */
	public function get_mcp_server(): McpServer {
		return $this->mcp_server;
	}

	/**
	 * Set the MCP server instance this tool belongs to.
	 *
	 * @param \WP\MCP\Core\McpServer $mcp_server The MCP server instance.
	 *
	 * @return void
	 */
	public function set_mcp_server( McpServer $mcp_server ): void {
		$this->mcp_server = $mcp_server;
	}

	/**
	 * Check if this prompt is builder-based (has direct execution capability).
	 *
	 * @return bool True if this prompt can execute directly, false if it needs abilities.
	 */
	public function is_builder_based(): bool {
		return false; // Default: requires abilities
	}

	/**
	 * Execute the prompt directly (for builder-based prompts).
	 *
	 * @param array $arguments The arguments passed to the prompt.
	 *
	 * @return array The prompt response.
	 * @throws \Exception If this prompt is not builder-based.
	 */
	public function execute_direct( array $arguments ): array {
		throw new \Exception( 'This prompt does not support direct execution' );
	}

	/**
	 * Check permission directly (for builder-based prompts).
	 *
	 * @param array $arguments The arguments passed to the prompt.
	 *
	 * @return bool True if execution is allowed.
	 * @throws \Exception If this prompt is not builder-based.
	 */
	public function check_permission_direct( array $arguments ): bool {
		throw new \Exception( 'This prompt does not support direct permission checking' );
	}
}
