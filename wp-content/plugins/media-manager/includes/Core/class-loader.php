<?php
declare(strict_types=1);
namespace MediaManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader
 *
 * Accumulates add_action() and add_filter() calls and registers them all at
 * once when run() is called. Keeps hook registration out of class constructors
 * and gives us a single place to see everything wired up.
 */
final class Loader {

	/** @var array[] */
	private array $actions = [];

	/** @var array[] */
	private array $filters = [];

	// -----------------------------------------------------------------------

	public function add_action(
		string $hook,
		object $component,
		string $callback,
		int $priority  = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	public function add_filter(
		string $hook,
		object $component,
		string $callback,
		int $priority  = 10,
		int $accepted_args = 1
	): void {
		$this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	// -----------------------------------------------------------------------

	/**
	 * Register all accumulated hooks with WordPress.
	 * Called once at the end of Plugin::run().
	 */
	public function run(): void {
		foreach ( $this->actions as $a ) {
			add_action(
				$a['hook'],
				[ $a['component'], $a['callback'] ],
				$a['priority'],
				$a['accepted_args']
			);
		}

		foreach ( $this->filters as $f ) {
			add_filter(
				$f['hook'],
				[ $f['component'], $f['callback'] ],
				$f['priority'],
				$f['accepted_args']
			);
		}
	}
}
