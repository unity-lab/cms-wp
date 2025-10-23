<?php
/**
 * WordPress MCP Tool class for representing MCP tools according to the specification.
 *
 * @package McpAdapter
 */

declare( strict_types=1 );

namespace WP\MCP\Domain\Tools;

use WP\MCP\Core\McpServer;
use WP_Ability;

/**
 * Represents an MCP tool according to the Model Context Protocol specification.
 *
 * Tools enable models to interact with external systems, such as querying databases,
 * calling APIs, or performing computations. Each tool is uniquely identified by a name
 * and includes metadata describing its schema.
 *
 * @link https://modelcontextprotocol.io/specification/2025-06-18/server/tools
 */
class McpTool {

	/**
	 * Ability name for the tool, used for registration.
	 *
	 * @var string
	 */
	private string $ability;

	/**
	 * Unique identifier for the tool.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Optional human-readable name of the tool for display purposes.
	 *
	 * @var string|null
	 */
	private ?string $title;

	/**
	 * Human-readable description of functionality.
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * JSON Schema defining expected parameters.
	 *
	 * @var array
	 */
	private array $input_schema;

	/**
	 * Optional JSON Schema defining expected output structure.
	 *
	 * @var array|null
	 */
	private ?array $output_schema;

	/**
	 * Optional properties describing tool behavior.
	 *
	 * @var array
	 */
	private array $annotations;

	/**
	 * The MCP server instance this tool belongs to.
	 *
	 * @var \WP\MCP\Core\McpServer
	 */
	private McpServer $mcp_server;

	/**
	 * Constructor for McpTool.
	 *
	 * @param string      $ability The ability name.
	 * @param string      $name Unique identifier for the tool.
	 * @param string      $description Human-readable description of functionality.
	 * @param array       $input_schema JSON Schema defining expected parameters.
	 * @param string|null $title Optional human-readable name for display.
	 * @param array|null  $output_schema Optional JSON Schema for output structure.
	 * @param array       $annotations Optional properties describing tool behavior.
	 */
	public function __construct(
		string $ability,
		string $name,
		string $description,
		array $input_schema,
		?string $title = null,
		?array $output_schema = null,
		array $annotations = array()
	) {
		$this->ability       = $ability;
		$this->name          = $name;
		$this->title         = $title;
		$this->description   = $description;
		$this->input_schema  = $input_schema;
		$this->output_schema = $output_schema;
		$this->annotations   = $annotations;
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
	 * Get the tool name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the tool title.
	 *
	 * @return string|null
	 */
	public function get_title(): ?string {
		return $this->title;
	}

	/**
	 * Get the tool description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Get the input schema.
	 *
	 * @return array
	 */
	public function get_input_schema(): array {
		return $this->input_schema;
	}

	/**
	 * Get the output schema.
	 *
	 * @return array|null
	 */
	public function get_output_schema(): ?array {
		return $this->output_schema;
	}

	/**
	 * Get the annotations.
	 *
	 * @return array
	 */
	public function get_annotations(): array {
		return $this->annotations;
	}

	/**
	 * Set the tool title.
	 *
	 * @param string|null $title The title to set.
	 *
	 * @return void
	 */
	public function set_title( ?string $title ): void {
		$this->title = $title;
	}

	/**
	 * Set the tool description.
	 *
	 * @param string $description The description to set.
	 *
	 * @return void
	 */
	public function set_description( string $description ): void {
		$this->description = $description;
	}

	/**
	 * Set the input schema.
	 *
	 * @param array $input_schema The input schema to set.
	 *
	 * @return void
	 */
	public function set_input_schema( array $input_schema ): void {
		$this->input_schema = $input_schema;
	}

	/**
	 * Set the output schema.
	 *
	 * @param array|null $output_schema The output schema to set.
	 *
	 * @return void
	 */
	public function set_output_schema( ?array $output_schema ): void {
		$this->output_schema = $output_schema;
	}

	/**
	 * Set the annotations.
	 *
	 * @param array $annotations The annotations to set.
	 *
	 * @return void
	 */
	public function set_annotations( array $annotations ): void {
		$this->annotations = $annotations;
	}

	/**
	 * Add an annotation.
	 *
	 * @param string $key The annotation key.
	 * @param mixed  $value The annotation value.
	 *
	 * @return void
	 */
	public function add_annotation( string $key, $value ): void {
		$this->annotations[ $key ] = $value;
	}

	/**
	 * Remove an annotation.
	 *
	 * @param string $key The annotation key to remove.
	 *
	 * @return void
	 */
	public function remove_annotation( string $key ): void {
		unset( $this->annotations[ $key ] );
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
	 * Convert the tool to an array representation according to MCP specification.
	 *
	 * @return array
	 */
	public function to_array(): array {
		$input_schema_for_json = empty( $this->input_schema )
			? array( 'type' => 'object' )
			: $this->input_schema;

		$tool_data = array(
			'name'        => $this->name,
			'description' => $this->description,
			'inputSchema' => $input_schema_for_json,
		);

		if ( ! is_null( $this->title ) ) {
			$tool_data['title'] = $this->title;
		}

		if ( ! is_null( $this->output_schema ) ) {
			$tool_data['outputSchema'] = $this->output_schema;
		}

		if ( ! empty( $this->annotations ) ) {
			$tool_data['annotations'] = $this->annotations;
		}

		return $tool_data;
	}

	/**
	 * Create an McpTool instance from an array.
	 *
	 * @param array     $data Array containing tool data.
	 * @param \WP\MCP\Core\McpServer $mcp_server The MCP server instance.
	 *
	 * @return self
	 * @throws \InvalidArgumentException If required fields are missing or validation fails.
	 */
	public static function from_array( array $data, McpServer $mcp_server ): self {
		$tool = new self(
			$data['ability'] ?? '',
			$data['name'] ?? '',
			$data['description'] ?? '',
			$data['inputSchema'] ?? array(),
			$data['title'] ?? null,
			$data['outputSchema'] ?? null,
			$data['annotations'] ?? array()
		);
		$tool->set_mcp_server( $mcp_server );

		return $tool->validate( "McpTool::from_array::{$data['name']}" );
	}

	/**
	 * Validate the tool according to MCP specification requirements.
	 * Uses the centralized McpToolValidator for consistent validation.
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

		$context_to_use = $context ?: "McpTool::{$this->name}";
		McpToolValidator::validate_tool_instance( $this, $context_to_use );

		return $this;
	}
}
