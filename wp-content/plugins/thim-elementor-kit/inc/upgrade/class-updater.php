<?php
namespace Thim_EL_Kit\Upgrade;

if ( ! class_exists( '\Thim_EL_Kit\Upgrade\Background_Process', false ) ) {
	include_once THIM_EKIT_PLUGIN_PATH . 'inc/upgrade/class-background-process.php';
}

use \Thim_EL_Kit\Upgrade\Background_Process;

class Updater extends Background_Process {

	public function __construct() {
		$this->prefix = 'wp_' . get_current_blog_id();
		$this->action = 'thim_ekit_updater';

		parent::__construct();
	}

	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	protected function task( $callback ) {
		// Include update file.
		if ( ! class_exists( '\Thim_EL_Kit\Upgrade\DB_Updates', false ) ) {
			include_once THIM_EKIT_PLUGIN_PATH . 'inc/upgrade/class-db-updates.php';
		}

		// Run the callback update.
		if ( method_exists( '\Thim_EL_Kit\Upgrade\DB_Updates', $callback ) ) {
			error_log( 'Thim Elementor Kit: Running ' . $callback );
			\Thim_EL_Kit\Upgrade\DB_Updates::instance()->{$callback}();
		}

		return false;
	}

	protected function complete() {
		update_option( 'thim_ekit_db_version', THIM_EKIT_VERSION );

		parent::complete();
	}

	public function is_memory_exceeded() {
		return $this->memory_exceeded();
	}
}
