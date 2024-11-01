<?php

/**
 * The dashboard-specific functionality of the plugin.
 */
class Table_Pixie_Admin {
	/**
	 * The ID of this plugin.
	 */
	private $plugin_id;

	/**
	 * The version of this plugin.
	 */
	private $plugin_version;

	/**
	 * The Screen ID of the admin page.
	 */
	private $page_hook;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_id      The name of this plugin.
	 * @param string $plugin_version The version of this plugin.
	 */
	public function __construct( $plugin_id, $plugin_version ) {

		$this->plugin_id      = $plugin_id;
		$this->plugin_version = $plugin_version;
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 */
	public function enqueue_styles() {
		if ( ! self::our_screen( get_current_screen(), $this->page_hook ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_id . '-admin',
			plugin_dir_url( __FILE__ ) . 'css/table-pixie-admin.css',
			array(),
			$this->plugin_version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the dashboard.
	 */
	public function enqueue_scripts() {
		if ( ! self::our_screen( get_current_screen(), $this->page_hook ) ) {
			return;
		}

		wp_enqueue_script( $this->plugin_id . '-admin',
			plugin_dir_url( __FILE__ ) . 'js/table-pixie-admin.js',
			array( 'jquery' ),
			$this->plugin_version,
			false
		);
	}

	/**
	 * Because we might re-direct we need to ensure no output has gone out yet.
	 */
	public function delay_output() {
		ob_start();
	}

	/**
	 * Add admin menu items.
	 */
	public function add_menu_items() {
		$admin_title = apply_filters( 'table_pixie_menu_title', __( 'WP Table Pixie', 'wp-table-pixie' ) );

		if ( is_multisite() && is_network_admin() ) {
			$page_hook = add_menu_page(
				$admin_title,
				$admin_title,
				'manage_network_options',
				'wp-table-pixie',
				array( $this, 'display_admin_page' ),
				'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB2ZXJzaW9uPSIxLjEiICAgaWQ9InN2ZzY2NjEiICAgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgICBoZWlnaHQ9IjEwMjQiICAgd2lkdGg9IjEwMjQiPiAgPGRlZnMgICAgIGlkPSJkZWZzNjY2MyIgLz4gIDxtZXRhZGF0YSAgICAgaWQ9Im1ldGFkYXRhNjY2NiI+ICAgIDxyZGY6UkRGPiAgICAgIDxjYzpXb3JrICAgICAgICAgcmRmOmFib3V0PSIiPiAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+ICAgICAgICA8ZGM6dHlwZSAgICAgICAgICAgcmRmOnJlc291cmNlPSJodHRwOi8vcHVybC5vcmcvZGMvZGNtaXR5cGUvU3RpbGxJbWFnZSIgLz4gICAgICAgIDxkYzp0aXRsZT48L2RjOnRpdGxlPiAgICAgIDwvY2M6V29yaz4gICAgPC9yZGY6UkRGPiAgPC9tZXRhZGF0YT4gIDxnICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLC0yOC4zNjIxNjEpIiAgICAgaWQ9ImxheWVyMSI+ICAgIDxwYXRoICAgICAgIGlkPSJwYXRoODQyNiIgICAgICAgc3R5bGU9ImZpbGw6IzIzMWYyMDtmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIgICAgICAgZD0ibSAxMzEuODk2MTUsNDc0LjgxNDI5IGMgLTAuMTAyMjMsLTIuMjE0NzggLTAuMTI3MjEsLTQuNDI5NTcgMC4wNDU0LC02LjY1ODU2IDAuMzgzODksLTQuODgzOSAxLjQwNTU0LC05LjY5NjgxIDMuMDUyNDMsLTE0LjQ1MjkyIDIzLjQ2ODI1LDIwLjE3NDQ2IDU0LjkwMTIzLDM3Ljg1MDE4IDkzLjYzMTY2LDUyLjUzMDI2IDM3LjY1MTQyLDE0LjI5Njc1IDgxLjA2Njk4LDI1LjQ3MDA5IDEyOS4wMzk4OSwzMy4xNzkyNSA0OC45OTUxMyw3Ljg5Mzc0IDEwMC45NDMzLDExLjkxMTYgMTU0LjMxMTIyLDExLjkxMTYgNTMuMzk2MywwIDEwNS4zMzAyOSwtNC4wMTc4NiAxNTQuMzUzOCwtMTEuOTExNiA0Ny45NzI5MiwtNy43MjMzNiA5MS4zODg0OCwtMTguODgyNSAxMjkuMDM5OSwtMzMuMTkzNDUgMzguNzAyMDMsLTE0LjY4MDA3IDcwLjEzNSwtMzIuMzQxNiA5My42MTc0NSwtNTIuNTE2MDYgMS42MzI3LDQuNzQxOTEgMi42NTQ5MSw5LjU2OTAyIDMuMDM4MjQsMTQuNDUyOTIgMC4xNzAzNywyLjIyODk5IDAuMTQxOTcsNC40NDM3OCAwLjA0NTQsNi42NTg1NiBsIDAuMDIyNywwIGMgMCwwLjIyNzE2IC0wLjAyMjcsMC40NTQzMSAtMC4wMjI3LDAuNjk2MjQgbCAwLDAuMDExMyBjIC0xLjM5MTM0LDMwLjM1Mzk5IC0yLjgxMTA4LDEwNC4yNjU0OSAtNC4yMDI0MiwxMzQuNjA1MjcgLTAuOTIzNCwxOS4xMjM4NiAtMTEuNjk4NjMsMzcuMzk1ODcgLTMwLjYwOTUzLDU0LjEwNjE4IC0xOS4wMjQ0NywxNi44MDk2OCAtNDYuMjgzNDIsMzEuOTg2NjggLTgwLjAwMjE3LDQ0Ljc5MjcgLTMzLjg3NDk0LDEyLjg3NzAxIC03NC4xOTU0NywyMy4yODM2OSAtMTE5LjA0NDk1LDMwLjUxMDE0IC00NC45MzQ2OCw3LjI1NDg2IC05NC4zNDE1MywxMS4yNDQzMyAtMTQ2LjIzMjkxLDExLjI0NDMzIC01MS44NzcyLDAgLTEwMS4yNjk4NCwtMy45ODk0NyAtMTQ2LjIwNDUyLC0xMS4yNDQzMyAtNDQuODYzNjMsLTcuMjI2NCAtODUuMTU1NzcsLTE3LjYzMzA4IC0xMTkuMDU5MDgsLTMwLjUxMDA5IC0zMy43MTgxOSwtMTIuODA1NDUgLTYwLjk2Mjk0LC0yNy45ODI0NSAtODAuMDAxNjIsLTQ0Ljc5MjE0IC0xOC45MTA4OSwtMTYuNzEwMyAtMjkuNjcyNSwtMzQuOTgyMzEgLTMwLjYwOTUyLC01NC4xMDYxNyAtMS4zOTEzNSwtMzAuMzM5NzggLTIuODExMDgsLTEwNC4yNTEyOSAtNC4yMDI0MywtMTM0LjYwNTI3IGwgMCwtMC4wMTE0IGMgLTAuMDIyNywtMC4yNDE5MiAtMC4wMjI3LC0wLjQ2Nzk0IC0wLjAyMjcsLTAuNjk2MjQgbCAwLjAyMjcsMCB6IG0gMzMuNzYxMzQsLTMxMS45MDIwMSBjIDIwLjExNzY3LC0xNi41ODI1MyA0OC40NTU2MiwtMzEuNDQ3MTggODIuOTI2ODQsLTQzLjgyNzI5IDM0LjI4NjY1LC0xMi4zMzc1MSA3NC42NzgxNiwtMjIuMjYxNDY4IDExOS4yNDM3MSwtMjkuMTA0NjA1IDQ0LjQ2NjE3LC02LjgwMDUzNiA5My4xMzQ3NSwtMTAuNTkxMjM0IDE0NC4xNDU5LC0xMC41OTEyMzQgNTEuMDI1MzQsMCA5OS42OTM5MywzLjc5MDY5OCAxNDQuMTYwMDksMTAuNTkxMjM0IDQ0LjU1MTM0LDYuODQzMTM3IDg0Ljk4NTQ1LDE2Ljc2NzA5NSAxMTkuMjU3OTEsMjkuMTA0NjA1IDM0LjQ1NzAxLDEyLjM4MDExIDYyLjc5NDk3LDI3LjI0NDc2IDgyLjkxMjY0LDQzLjgyNzI5IDIwLjI1OTY1LDE2LjY2NzcxIDMyLjIyODAzLDM1LjE2Njg4IDMzLjcxODc1LDU0Ljc0NTA1IDAuMTcwMzcsMi4yMDA1OSAwLjE0MTk3LDQuNDE1MzkgMC4wNDU0LDYuNjMwMTggbCAwLjAyMjcsMCBjIDAsMC4yMTIzOSAtMC4wMjI3LDAuNDY3OTQgLTAuMDIyNywwLjcwOTg2IC0xLjM5MTM0LDMwLjM1Mzk4IC0yLjgxMTA4LDEwNC4yNjU0OSAtNC4yMDI0MiwxMzQuNTkxMDcgLTAuOTIzNCwxOS4xMzgwNSAtMTEuNjk4NjMsMzcuNDM4NDYgLTMwLjYwOTUzLDU0LjEyMDM3IC0xOS4wMjQ0NywxNi44MjM4OSAtNDYuMjgzNDIsMzIuMDAwODcgLTgwLjAwMjE3LDQ0LjgwNjkgLTMzLjg3NDk0LDEyLjg2MjgyIC03NC4xOTU0NywyMy4yODM2OSAtMTE5LjA0NDk1LDMwLjUxMDE1IC00NC45MzQ2OCw3LjI0MDY1IC05NC4zNDE1MywxMS4yNDQzMSAtMTQ2LjIzMjkxLDExLjI0NDMxIC01MS44NzcxOSwwIC0xMDEuMjY5ODQsLTQuMDAzNjYgLTE0Ni4yMDQ1MiwtMTEuMjQ0MzEgLTQ0Ljg2MzY5LC03LjIyNjQ2IC04NS4xNTU4MiwtMTcuNjQ3MzMgLTExOS4wNTkxNCwtMzAuNTEwMTUgLTMzLjcxODc2LC0xMi44MDYwMyAtNjAuOTYzNTEsLTI3Ljk4MzAxIC04MC4wMDIxOCwtNDQuODA2OSAtMTguOTEwODksLTE2LjY4MTkxIC0yOS42NzI1LC0zNC45ODIzMiAtMzAuNjA5NTMsLTU0LjEyMDM3IC0xLjM5MTM0LC0zMC4zMjU1OCAtMi44MTEwOCwtMTA0LjIzNzA5IC00LjIwMjQyLC0xMzQuNTkxMDcgLTAuMDIyNywtMC4yNDE5MiAtMC4wMjI3LC0wLjQ5NzQ3IC0wLjAyMjcsLTAuNzA5ODYgbCAwLjAyMjcsMCBjIC0wLjEwMjIzLC0yLjIxNDc5IC0wLjEyNzIxLC00LjQyOTU5IDAuMDQ1NCwtNi42MzAxOCAxLjUwNDkyLC0xOS41NzgxNyAxMy40NzMzMSwtMzguMDc3MzQgMzMuNzE4NzUsLTU0Ljc0NTA1IG0gNjkxLjU5NzExLDc1MS44NTAxMSBjIC0xOS4wMjQ0NywxNi44MjM4OCAtNDYuMjgzNDMsMzIuMDAwODcgLTgwLjAwMjE4LDQ0LjgwNjkgLTMzLjg3NDkzLDEyLjg2MjgxIC03NC4xOTU0NiwyMy4yNjk0OCAtMTE5LjA0NDk0LDMwLjQ5NTk1IC00NC45MzQ2OCw3LjI1NDg2IC05NC4zNDE1MywxMS4yNTg0NiAtMTQ2LjIzMjkxLDExLjI1ODQ2IC01MS44NzcyLDAgLTEwMS4yNjk4NCwtNC4wMDM2IC0xNDYuMjA0NTIsLTExLjI1ODQ2IC00NC44NjM2OSwtNy4yMjY0NyAtODUuMTU1ODMsLTE3LjYzMzE0IC0xMTkuMDU5MTUsLTMwLjQ5NTk1IC0zMy43MTg3NSwtMTIuODA2MDMgLTYwLjk2MzUsLTI3Ljk4MzAyIC04MC4wMDIxOCwtNDQuODA2OSAtMTguOTEwODksLTE2LjY4MTkxIC0yOS42NzI1LC0zNC45OTY1MSAtMzAuNjA5NTIsLTU0LjEyMDM4IC0xLjM5MTM1LC0zMC4zMjU1OCAtMi44MTEwOCwtMTA0LjIzNzA4IC00LjIwMjQzLC0xMzQuNTkxMDYgbCAwLC0wLjAxMTQgYyAtMC4wMjI3LC0wLjIyNzE2IC0wLjAyMjcsLTAuNDgyNzEgLTAuMDIyNywtMC42OTYyNCBsIDAuMDIyNywwIGMgLTAuMTAyMjIsLTIuMjE0NzkgLTAuMTI3MjEsLTQuNDI5NTggMC4wNDU0LC02LjY0NDM3IDAuMzgzOSwtNC44OTgwOSAxLjQwNTU0LC05LjcyNTIgMy4wNTI0NCwtMTQuNDgxMzIgMjMuNDI1NjYsMjAuMTE3NjcgNTQuODMwMjQsMzcuODA3NiA5My42MDMyNiw1Mi41MzAyOCAzNy41MDk0NSwxNC4yNjgzNSA4MC45MjUwMSwyNS40Mjc0OCAxMjkuMDM5ODksMzMuMTc5MjUgNDguODUzMTYsNy45MDc5MyAxMDAuODAxMzMsMTEuODk3MzkgMTU0LjMzOTYyLDExLjg5NzM5IDUzLjU2NjY3LDAgMTA1LjUwMDY1LC0zLjk4OTQ2IDE1NC4zODIxOSwtMTEuODk3MzkgNDguMTE0ODksLTcuNzUxNzcgOTEuNTMwNDUsLTE4LjkxMDkgMTI5LjAzOTksLTMzLjE5MzQ1IDM4Ljc1ODgyLC0xNC43MzY4OCA3MC4xNzc1OSwtMzIuMzk4NDEgOTMuNTYwNjYsLTUyLjUxNjA4IDEuNjYxMSw0Ljc1NjEyIDIuNjgzMzEsOS41ODMyMyAzLjA2NjY0LDE0LjQ4MTMyIDAuMTcwMzcsMi4yMTQ3OSAwLjE0MTk3LDQuNDI5NTggMC4wNDU0LDYuNjQ0MzcgbCAwLjAyMjcsMCBjIDAsMC4yMTIzOSAtMC4wMjI3LDAuNDY3OTQgLTAuMDIyNywwLjY5NjI0IGwgMCwwLjAxMTQgYyAtMS4zOTEzNCwzMC4zNTM5OCAtMi44MTEwOCwxMDQuMjY1NDggLTQuMjAyNDMsMTM0LjU5MTA2IC0wLjkyMzM5LDE5LjEyMzg3IC0xMS42OTg2MiwzNy40Mzg0NyAtMzAuNjA5NTIsNTQuMTIwMzggbSA4My43MDc2OSwtMzcwLjEzOTYzIGMgMC42ODE0OCwtMjUuMDQ0MTYgMS4zNzcxNSwtNTAuOTExNzYgMi4wNDQ0MiwtNjUuODMzMjEgMC4wOTk5LC0xLjMwNjE2IDAuMTg0NTYsLTIuNjI2NTEgMC4xODQ1NiwtMy45NzUyNiAwLC0wLjI4Mzk1IC0wLjAyODQsLTAuNTU0MjcgLTAuMDI4NCwtMC44MzgyMSAwLjA3MDQsLTMuNDIxNTYgLTAuMDI4NCwtNi42NTg1NyAtMC4yNjk3NCwtOS44MTAzOCAtMS4wNzksLTE0LjI4MjU1IC01LjU2NTM3LC0zMy4wMDg4OCAtMTguNDcwNzksLTUzLjA2OTc3IDEwLjE5MzcyLC0xOC41ODQzNSAxMy43NzE0NiwtMzUuODA1NzcgMTQuMzk2MTQsLTQ5LjA4MDMxIDAuNzI0MDcsLTE1LjY0NTUgMS40MzM5NCwtNDEuMDQ0NiAyLjE0MzgsLTY3LjkzNDQyIDAuNjgxNDgsLTI1LjAyOTk1IDEuMzc3MTUsLTUwLjg4MzM3IDIuMDQ0NDIsLTY1LjgxOSAwLjA5OTksLTEuMzA2MTYgMC4xODQ1NiwtMi42NTQ5MSAwLjE4NDU2LC0zLjk3NTI2IDAsLTAuMjcwMzIgLTAuMDI4NCwtMC41Njc5IC0wLjAyODQsLTAuODM4MjIgMC4wNzA0LC0zLjQzNTc2IC0wLjAyODQsLTYuNjg2OTYgLTAuMjY5NzQsLTkuODEwMzggLTEuNzMyMDgsLTIyLjg4NjE2IC0xMi4xOTU1NSwtNTcuMjU3OTkgLTUyLjE0Njk1LC05MC4xMzkxIEMgODY2LjIxMDczLDEwMy4yNjc5OSA4MzMuMTg3NjUsODUuNjIwNjU4IDc5Mi42Mzk5Niw3MS4wNTQxNTUgNzU0Ljg2MDc2LDU3LjQ2NzI3MSA3MTEuNTQ0NTgsNDYuODQ3NjM3IDY2My44Njk4MSwzOS41MjE4IDYxNS40NDI1OSwzMi4xMTA3NzQgNTY0LjM0NjI1LDI4LjM2MjY2NiA1MTEuOTg2MzUsMjguMzYyNjY2IGMgLTUyLjM1OTksMCAtMTAzLjQ0MjAzLDMuNzQ4MTA4IC0xNTEuODY5MjcsMTEuMTU5MTM0IC00Ny42NDYzNyw3LjMyNTgzNyAtOTAuOTkwOTQsMTcuOTQ1NDcxIC0xMjguNzk4NTQsMzEuNTQ2NTU1IC00MC41MTkyOSwxNC41NTIzMDMgLTczLjU0MjM3LDMyLjE5OTYzNSAtOTguMTE4MDIsNTIuNDU5Mjg1IC0zOS45MjMwMDUsMzIuODgxMSAtNTAuMzg2NDY1LDY3LjIzODczIC01Mi4xMzI3NDUsOTAuMjY2ODcgLTAuMjcwMzEsMy4zMzYzOCAtMC4zNDA3Myw2Ljg5OTkyIC0wLjIyNzE1LDEwLjczMzIyIDAsMC43NjY2NSAwLjAzNDEsMS41MTkxMSAwLjA1NjgsMi4yNTczOCBsIDAuMDQ1NCwwLjkwODYzIGMgMC42NTMwOCwxNC40MTAzMyAxLjM3NzE0LDQwLjg0NTgzIDIuMDcyODEsNjYuMzg2ODkgMC43MjM1LDI2Ljg4OTgzIDEuNDE5NzQsNTIuMjg4OTIgMi4xMTU0MSw2Ny44NjM0NCAwLjYzODMyLDEzLjMzMTMyIDQuMjMwODIsMzAuNTUyNzQgMTQuNDI0NTMsNDkuMTUxMjkgLTEyLjkwNTQxLDIwLjA4OTI4IC0xNy4zOTE3OCwzOC44NDQwMSAtMTguNDg0OTgsNTMuMTk3NTUgLTAuMjU1NTUsMy4zNjQ3NyAtMC4zNDA3Myw2LjkyODMxIC0wLjIyNzE2LDEwLjc2MTYgMCwwLjc1MTg5IDAuMDM0MSwxLjUxOTEyIDAuMDU2OCwyLjI4NTc3IGwgMC4wNDU0LDAuODUxODUgYyAwLjY1MzA4LDE0LjQzODczIDEuMzc3MTQsNDAuODc0MjIgMi4wNzI4MSw2Ni40Mjk0OSAwLjcyMzUsMjYuODg5ODIgMS40MTk3NCw1Mi4yODg5MSAyLjExNTQxLDY3Ljg0OTI0IDAuNjM4MzIsMTMuMzMxMzIgNC4yMzA4MiwzMC41NTI3MyAxNC40NTI5Miw0OS4xMjI4OSAtMTIuOTMzOCwyMC4wODkyOCAtMTcuNDIwMTcsMzguODcyNCAtMTguNTEzMzcsNTMuMjQwMTQgLTAuMjcwMzIsMy4zNTA1OCAtMC4zNDA3Myw2LjkxNDEyIC0wLjIyNzE2LDEwLjc0NzQxIDAsMC43NjY2NiAwLjAzNDEsMS41MzMzMiAwLjA1NjgsMi4yODU3OCBsIDAuMDQ1NCwwLjg1MTg0IGMgMC42NTMwNywxNC40NTI5MiAxLjM3NzE0LDQwLjg0NTgzIDIuMDcyODEsNjYuNDAxMSAwLjcyMzUsMjYuODc1NjEgMS40MTk3NCw1Mi4yODg5MSAyLjExNTQxLDY3Ljg2MzQyIDEuMDkyNjMsMjIuODE1MTggMTAuMjY0NjksNTYuOTAzMDcgNDcuODAyNTQ1LDkwLjA1MzkyIDIzLjYyNDQyLDIwLjg0MTc0IDU1LjgyNDA2LDM5LjA5OTU2IDk1LjcwNDQ3LDU0LjIzMzk0IDM3LjU1MjA0LDE0LjI4MjYgODAuOTY3NiwyNS40NTU5IDEyOS4wMTE1LDMzLjE5MzUgNDguOTUyNTMsNy44Nzk1IDEwMC44ODY1MSwxMS44OTc0IDE1NC4zMzk2MSwxMS44OTc0IDUzLjQ2NzI5LDAgMTA1LjQwMTI4LC00LjAxNzkgMTU0LjM1MzgsLTExLjkxMTYgNDguMDcyMywtNy43Mzc2IDkxLjQ4Nzg2LC0xOC45MTA5IDEyOS4wMzk5LC0zMy4xNzkzIDM5Ljg4MDQxLC0xNS4xMzQzOCA3Mi4wODAwNCwtMzMuMzkyMiA5NS43MDQ0NywtNTQuMjc2NTMgMzcuNTA5NDUsLTMzLjEwODI2IDQ2LjY4MDk1LC02Ny4xOTYxNSA0Ny43NDU3NiwtODkuOTQwMzQgMC43MjQwNiwtMTUuNjQ1NSAxLjQzMzkzLC00MS4wNDQ2IDIuMTQzOCwtNjcuOTIwMjEgMC42ODE0NywtMjUuMDQ0MTYgMS4zNzcxNSwtNTAuODgzMzggMi4wNDQ0MSwtNjUuODE5MDEgMC4xLC0xLjMwNjE2IDAuMTg0NTcsLTIuNjQwNzEgMC4xODQ1NywtMy45NzUyNyAwLC0wLjI4Mzk0IC0wLjAyODQsLTAuNTY3ODkgLTAuMDI4NCwtMC44MzcwNyAwLjA3MDQsLTMuNDM1NzcgLTAuMDI4NCwtNi42NzI3NyAtMC4yNjk3NCwtOS44MTAzOCAtMS4wNzksLTE0LjI4MjU2IC01LjU2NTM3LC0zMy4wMjMwOSAtMTguNDcwNzgsLTUzLjA5ODE3IDEwLjE5MzcxLC0xOC41NTU5NiAxMy43NzE0NSwtMzUuNzQ4OTggMTQuMzk2MTQsLTQ5LjAzNzcxIDAuNzI0MDYsLTE1LjY0NTUxIDEuNDMzOTMsLTQxLjA0NDYgMi4xNDM4LC02Ny45MzQ0MiIgLz4gICAgPGcgICAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMC44MDAwMDAyNCwwLDAsLTAuMjUzMDYxNDcsLTE2My45MzU4NSwyMTEuNDQzOTgpIiAgICAgICBpZD0iZzMzNzYiPiAgICAgIDxwYXRoICAgICAgICAgaWQ9InBhdGgzMzc4IiAgICAgICAgIHN0eWxlPSJmaWxsOiNiY2JlYzA7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlOm5vbmUiICAgICAgICAgZD0ibSAxMDA0LjA1MjIsLTQxLjY4MzE3OCBjIDQ1LjQxNDIsMCA4Mi4yNDU2LDM2LjgzNDAwMDggODIuMjQ1Niw4Mi4yNTA4NzYgMCw0NS40MTc3NTEgLTM2LjgzMTQsODIuMjUwMDAyIC04Mi4yNDU2LDgyLjI1MDAwMiAtNDUuNDE3NzcsMCAtODIuMjQ2NTIsLTM2LjgzMjI1MSAtODIuMjQ2NTIsLTgyLjI1MDAwMiAwLC00NS40MTY4NzUyIDM2LjgyODc1LC04Mi4yNTA4NzYgODIuMjQ2NTIsLTgyLjI1MDg3NiBNIDg1MS4yMDk4MSwyNS42NjkwNzMgYyAwLDg4Ljg3NTQ5NyA1NC4zMTgyNSwxNjguNDYyMDA3IDE1Mi44NDIzOSwxNjguNDYyMDA3IDg0Ljc5MTksMCAxNTMuNTUyLC02OC43NjgwMSAxNTMuNTUyLC0xNTMuNTYzMzgyIDAsLTg0Ljc5NTM3NiAtNjguNzYwMSwtMTUzLjU2MTYyOCAtMTUzLjU1MiwtMTUzLjU2MTYyOCAtMzAuMjY0NTIsMCAtNTguNDg0MTQsOC43NTc4OCAtODIuMjY3NTIsMjMuODgwNTAyIGwgMCwtMjE0LjM2ODg3MiBjIDE1OC4xODA3MiwzNS4xMDUgMjc2LjUwMjYyLDE3Ni4yNTgyNSAyNzYuNTAyNjIsMzQ0Ljk5NDk5OCAwLDE5NS4xMzYzODIgLTE1OC4yNDExLDM1My4zODM2MzIgLTM1My4zNjYxMiwzNTMuMzgzNjMyIC03OS4wMDQ2MiwwIC0xNTEuOTYzODcsLTI1Ljk0MiAtMjEwLjgxODEyLC02OS43NzMzOCBsIDAsOTkuODM5MjUgYyA2Mi41MzAxMiwzNC40NjI3NSAxMzQuMzg3NzUsNTQuMDcyMzggMjEwLjgxODEyLDU0LjA3MjM4IDI0MS41ODIyMiwwIDQzNy40OTgyMiwtMTk1LjkyNTYzIDQzNy40OTgyMiwtNDM3LjUyMTg4MiAwLC0yMzkuNDk0NDk4IC0xOTIuNTIyNywtNDM0LjExMTEyOCAtNDMxLjIwOTU5LC00MzcuNDc4MTI4IGwgMCwyNy43NTg1IDAsMzkzLjg3NjAwMyB6IiAvPiAgICA8L2c+ICAgIDxnICAgICAgIHRyYW5zZm9ybT0ibWF0cml4KDAuNzAwMDAwMjIsMCwwLC0wLjIyMTQyODc5LDM4NC4xMjYxNywxNzkuODg1MjQpIiAgICAgICBpZD0iZzMzODAiPiAgICAgIDxwYXRoICAgICAgICAgaWQ9InBhdGgzMzgyIiAgICAgICAgIHN0eWxlPSJmaWxsOiM1MjY1OGQ7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlOm5vbmUiICAgICAgICAgZD0ibSAwLDAgYyA1MS45MDQsMCA5My45OTYsLTQyLjA5NCA5My45OTYsLTkzLjk5OSAwLC01MS45MDYgLTQyLjA5MiwtOTQgLTkzLjk5NiwtOTQgLTUxLjkwNiwwIC05My45OTYsNDIuMDk0IC05My45OTYsOTQgQyAtOTMuOTk2LC00Mi4wOTQgLTUxLjkwNiwwIDAsMCBtIC0yMjEuMTcsLTk1LjA4MSBjIDAsNjcuOTg3IDE2LjgwOSwxMzIuMDU3IDQ2LjQ5MywxODguMjc2IGwgMCwtMTcwLjE2NiBjIDAsLTEwMS41NzMgNjIuMDc4LC0xOTIuNTI4IDE3NC42NzcsLTE5Mi41MjggOTYuOTA1LDAgMTc1LjQ5LDc4LjU5MSAxNzUuNDksMTc1LjUgQyAxNzUuNDksMi45MDkgOTYuOTA1LDgxLjQ5OSAwLDgxLjQ5OSBjIC0zNC41ODYsMCAtNjYuODM4LC0xMC4wMSAtOTQuMDE1LC0yNy4yOTIgbCAwLDE5NS4zNTYgMCw3MS44NzggQyAtMjI4LjYxMywyMzEuODI4IC0zMTcuMzIzLDc4LjcxMiAtMzE3LjMyMywtOTUuMDgxIGMgMCwtMjY0LjMzOSAyMDUuMjI1LC00ODAuODQgNDY0Ljk5MywtNDk4LjgxNyBsIDAsOTYuNDUgYyAtMjA2LjYxNiwxNy43NTEgLTM2OC44NCwxOTEuMTUyIC0zNjguODQsNDAyLjM2NyIgLz4gICAgPC9nPiAgPC9nPjwvc3ZnPg=='
			);
		} else {
			$page_hook = add_menu_page(
				$admin_title,
				$admin_title,
				'manage_options',
				'wp-table-pixie',
				array( $this, 'display_admin_page' ),
				'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB2ZXJzaW9uPSIxLjEiICAgaWQ9InN2ZzY2NjEiICAgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgICBoZWlnaHQ9IjEwMjQiICAgd2lkdGg9IjEwMjQiPiAgPGRlZnMgICAgIGlkPSJkZWZzNjY2MyIgLz4gIDxtZXRhZGF0YSAgICAgaWQ9Im1ldGFkYXRhNjY2NiI+ICAgIDxyZGY6UkRGPiAgICAgIDxjYzpXb3JrICAgICAgICAgcmRmOmFib3V0PSIiPiAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+ICAgICAgICA8ZGM6dHlwZSAgICAgICAgICAgcmRmOnJlc291cmNlPSJodHRwOi8vcHVybC5vcmcvZGMvZGNtaXR5cGUvU3RpbGxJbWFnZSIgLz4gICAgICAgIDxkYzp0aXRsZT48L2RjOnRpdGxlPiAgICAgIDwvY2M6V29yaz4gICAgPC9yZGY6UkRGPiAgPC9tZXRhZGF0YT4gIDxnICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLC0yOC4zNjIxNjEpIiAgICAgaWQ9ImxheWVyMSI+ICAgIDxwYXRoICAgICAgIGlkPSJwYXRoODQyNiIgICAgICAgc3R5bGU9ImZpbGw6IzIzMWYyMDtmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIgICAgICAgZD0ibSAxMzEuODk2MTUsNDc0LjgxNDI5IGMgLTAuMTAyMjMsLTIuMjE0NzggLTAuMTI3MjEsLTQuNDI5NTcgMC4wNDU0LC02LjY1ODU2IDAuMzgzODksLTQuODgzOSAxLjQwNTU0LC05LjY5NjgxIDMuMDUyNDMsLTE0LjQ1MjkyIDIzLjQ2ODI1LDIwLjE3NDQ2IDU0LjkwMTIzLDM3Ljg1MDE4IDkzLjYzMTY2LDUyLjUzMDI2IDM3LjY1MTQyLDE0LjI5Njc1IDgxLjA2Njk4LDI1LjQ3MDA5IDEyOS4wMzk4OSwzMy4xNzkyNSA0OC45OTUxMyw3Ljg5Mzc0IDEwMC45NDMzLDExLjkxMTYgMTU0LjMxMTIyLDExLjkxMTYgNTMuMzk2MywwIDEwNS4zMzAyOSwtNC4wMTc4NiAxNTQuMzUzOCwtMTEuOTExNiA0Ny45NzI5MiwtNy43MjMzNiA5MS4zODg0OCwtMTguODgyNSAxMjkuMDM5OSwtMzMuMTkzNDUgMzguNzAyMDMsLTE0LjY4MDA3IDcwLjEzNSwtMzIuMzQxNiA5My42MTc0NSwtNTIuNTE2MDYgMS42MzI3LDQuNzQxOTEgMi42NTQ5MSw5LjU2OTAyIDMuMDM4MjQsMTQuNDUyOTIgMC4xNzAzNywyLjIyODk5IDAuMTQxOTcsNC40NDM3OCAwLjA0NTQsNi42NTg1NiBsIDAuMDIyNywwIGMgMCwwLjIyNzE2IC0wLjAyMjcsMC40NTQzMSAtMC4wMjI3LDAuNjk2MjQgbCAwLDAuMDExMyBjIC0xLjM5MTM0LDMwLjM1Mzk5IC0yLjgxMTA4LDEwNC4yNjU0OSAtNC4yMDI0MiwxMzQuNjA1MjcgLTAuOTIzNCwxOS4xMjM4NiAtMTEuNjk4NjMsMzcuMzk1ODcgLTMwLjYwOTUzLDU0LjEwNjE4IC0xOS4wMjQ0NywxNi44MDk2OCAtNDYuMjgzNDIsMzEuOTg2NjggLTgwLjAwMjE3LDQ0Ljc5MjcgLTMzLjg3NDk0LDEyLjg3NzAxIC03NC4xOTU0NywyMy4yODM2OSAtMTE5LjA0NDk1LDMwLjUxMDE0IC00NC45MzQ2OCw3LjI1NDg2IC05NC4zNDE1MywxMS4yNDQzMyAtMTQ2LjIzMjkxLDExLjI0NDMzIC01MS44NzcyLDAgLTEwMS4yNjk4NCwtMy45ODk0NyAtMTQ2LjIwNDUyLC0xMS4yNDQzMyAtNDQuODYzNjMsLTcuMjI2NCAtODUuMTU1NzcsLTE3LjYzMzA4IC0xMTkuMDU5MDgsLTMwLjUxMDA5IC0zMy43MTgxOSwtMTIuODA1NDUgLTYwLjk2Mjk0LC0yNy45ODI0NSAtODAuMDAxNjIsLTQ0Ljc5MjE0IC0xOC45MTA4OSwtMTYuNzEwMyAtMjkuNjcyNSwtMzQuOTgyMzEgLTMwLjYwOTUyLC01NC4xMDYxNyAtMS4zOTEzNSwtMzAuMzM5NzggLTIuODExMDgsLTEwNC4yNTEyOSAtNC4yMDI0MywtMTM0LjYwNTI3IGwgMCwtMC4wMTE0IGMgLTAuMDIyNywtMC4yNDE5MiAtMC4wMjI3LC0wLjQ2Nzk0IC0wLjAyMjcsLTAuNjk2MjQgbCAwLjAyMjcsMCB6IG0gMzMuNzYxMzQsLTMxMS45MDIwMSBjIDIwLjExNzY3LC0xNi41ODI1MyA0OC40NTU2MiwtMzEuNDQ3MTggODIuOTI2ODQsLTQzLjgyNzI5IDM0LjI4NjY1LC0xMi4zMzc1MSA3NC42NzgxNiwtMjIuMjYxNDY4IDExOS4yNDM3MSwtMjkuMTA0NjA1IDQ0LjQ2NjE3LC02LjgwMDUzNiA5My4xMzQ3NSwtMTAuNTkxMjM0IDE0NC4xNDU5LC0xMC41OTEyMzQgNTEuMDI1MzQsMCA5OS42OTM5MywzLjc5MDY5OCAxNDQuMTYwMDksMTAuNTkxMjM0IDQ0LjU1MTM0LDYuODQzMTM3IDg0Ljk4NTQ1LDE2Ljc2NzA5NSAxMTkuMjU3OTEsMjkuMTA0NjA1IDM0LjQ1NzAxLDEyLjM4MDExIDYyLjc5NDk3LDI3LjI0NDc2IDgyLjkxMjY0LDQzLjgyNzI5IDIwLjI1OTY1LDE2LjY2NzcxIDMyLjIyODAzLDM1LjE2Njg4IDMzLjcxODc1LDU0Ljc0NTA1IDAuMTcwMzcsMi4yMDA1OSAwLjE0MTk3LDQuNDE1MzkgMC4wNDU0LDYuNjMwMTggbCAwLjAyMjcsMCBjIDAsMC4yMTIzOSAtMC4wMjI3LDAuNDY3OTQgLTAuMDIyNywwLjcwOTg2IC0xLjM5MTM0LDMwLjM1Mzk4IC0yLjgxMTA4LDEwNC4yNjU0OSAtNC4yMDI0MiwxMzQuNTkxMDcgLTAuOTIzNCwxOS4xMzgwNSAtMTEuNjk4NjMsMzcuNDM4NDYgLTMwLjYwOTUzLDU0LjEyMDM3IC0xOS4wMjQ0NywxNi44MjM4OSAtNDYuMjgzNDIsMzIuMDAwODcgLTgwLjAwMjE3LDQ0LjgwNjkgLTMzLjg3NDk0LDEyLjg2MjgyIC03NC4xOTU0NywyMy4yODM2OSAtMTE5LjA0NDk1LDMwLjUxMDE1IC00NC45MzQ2OCw3LjI0MDY1IC05NC4zNDE1MywxMS4yNDQzMSAtMTQ2LjIzMjkxLDExLjI0NDMxIC01MS44NzcxOSwwIC0xMDEuMjY5ODQsLTQuMDAzNjYgLTE0Ni4yMDQ1MiwtMTEuMjQ0MzEgLTQ0Ljg2MzY5LC03LjIyNjQ2IC04NS4xNTU4MiwtMTcuNjQ3MzMgLTExOS4wNTkxNCwtMzAuNTEwMTUgLTMzLjcxODc2LC0xMi44MDYwMyAtNjAuOTYzNTEsLTI3Ljk4MzAxIC04MC4wMDIxOCwtNDQuODA2OSAtMTguOTEwODksLTE2LjY4MTkxIC0yOS42NzI1LC0zNC45ODIzMiAtMzAuNjA5NTMsLTU0LjEyMDM3IC0xLjM5MTM0LC0zMC4zMjU1OCAtMi44MTEwOCwtMTA0LjIzNzA5IC00LjIwMjQyLC0xMzQuNTkxMDcgLTAuMDIyNywtMC4yNDE5MiAtMC4wMjI3LC0wLjQ5NzQ3IC0wLjAyMjcsLTAuNzA5ODYgbCAwLjAyMjcsMCBjIC0wLjEwMjIzLC0yLjIxNDc5IC0wLjEyNzIxLC00LjQyOTU5IDAuMDQ1NCwtNi42MzAxOCAxLjUwNDkyLC0xOS41NzgxNyAxMy40NzMzMSwtMzguMDc3MzQgMzMuNzE4NzUsLTU0Ljc0NTA1IG0gNjkxLjU5NzExLDc1MS44NTAxMSBjIC0xOS4wMjQ0NywxNi44MjM4OCAtNDYuMjgzNDMsMzIuMDAwODcgLTgwLjAwMjE4LDQ0LjgwNjkgLTMzLjg3NDkzLDEyLjg2MjgxIC03NC4xOTU0NiwyMy4yNjk0OCAtMTE5LjA0NDk0LDMwLjQ5NTk1IC00NC45MzQ2OCw3LjI1NDg2IC05NC4zNDE1MywxMS4yNTg0NiAtMTQ2LjIzMjkxLDExLjI1ODQ2IC01MS44NzcyLDAgLTEwMS4yNjk4NCwtNC4wMDM2IC0xNDYuMjA0NTIsLTExLjI1ODQ2IC00NC44NjM2OSwtNy4yMjY0NyAtODUuMTU1ODMsLTE3LjYzMzE0IC0xMTkuMDU5MTUsLTMwLjQ5NTk1IC0zMy43MTg3NSwtMTIuODA2MDMgLTYwLjk2MzUsLTI3Ljk4MzAyIC04MC4wMDIxOCwtNDQuODA2OSAtMTguOTEwODksLTE2LjY4MTkxIC0yOS42NzI1LC0zNC45OTY1MSAtMzAuNjA5NTIsLTU0LjEyMDM4IC0xLjM5MTM1LC0zMC4zMjU1OCAtMi44MTEwOCwtMTA0LjIzNzA4IC00LjIwMjQzLC0xMzQuNTkxMDYgbCAwLC0wLjAxMTQgYyAtMC4wMjI3LC0wLjIyNzE2IC0wLjAyMjcsLTAuNDgyNzEgLTAuMDIyNywtMC42OTYyNCBsIDAuMDIyNywwIGMgLTAuMTAyMjIsLTIuMjE0NzkgLTAuMTI3MjEsLTQuNDI5NTggMC4wNDU0LC02LjY0NDM3IDAuMzgzOSwtNC44OTgwOSAxLjQwNTU0LC05LjcyNTIgMy4wNTI0NCwtMTQuNDgxMzIgMjMuNDI1NjYsMjAuMTE3NjcgNTQuODMwMjQsMzcuODA3NiA5My42MDMyNiw1Mi41MzAyOCAzNy41MDk0NSwxNC4yNjgzNSA4MC45MjUwMSwyNS40Mjc0OCAxMjkuMDM5ODksMzMuMTc5MjUgNDguODUzMTYsNy45MDc5MyAxMDAuODAxMzMsMTEuODk3MzkgMTU0LjMzOTYyLDExLjg5NzM5IDUzLjU2NjY3LDAgMTA1LjUwMDY1LC0zLjk4OTQ2IDE1NC4zODIxOSwtMTEuODk3MzkgNDguMTE0ODksLTcuNzUxNzcgOTEuNTMwNDUsLTE4LjkxMDkgMTI5LjAzOTksLTMzLjE5MzQ1IDM4Ljc1ODgyLC0xNC43MzY4OCA3MC4xNzc1OSwtMzIuMzk4NDEgOTMuNTYwNjYsLTUyLjUxNjA4IDEuNjYxMSw0Ljc1NjEyIDIuNjgzMzEsOS41ODMyMyAzLjA2NjY0LDE0LjQ4MTMyIDAuMTcwMzcsMi4yMTQ3OSAwLjE0MTk3LDQuNDI5NTggMC4wNDU0LDYuNjQ0MzcgbCAwLjAyMjcsMCBjIDAsMC4yMTIzOSAtMC4wMjI3LDAuNDY3OTQgLTAuMDIyNywwLjY5NjI0IGwgMCwwLjAxMTQgYyAtMS4zOTEzNCwzMC4zNTM5OCAtMi44MTEwOCwxMDQuMjY1NDggLTQuMjAyNDMsMTM0LjU5MTA2IC0wLjkyMzM5LDE5LjEyMzg3IC0xMS42OTg2MiwzNy40Mzg0NyAtMzAuNjA5NTIsNTQuMTIwMzggbSA4My43MDc2OSwtMzcwLjEzOTYzIGMgMC42ODE0OCwtMjUuMDQ0MTYgMS4zNzcxNSwtNTAuOTExNzYgMi4wNDQ0MiwtNjUuODMzMjEgMC4wOTk5LC0xLjMwNjE2IDAuMTg0NTYsLTIuNjI2NTEgMC4xODQ1NiwtMy45NzUyNiAwLC0wLjI4Mzk1IC0wLjAyODQsLTAuNTU0MjcgLTAuMDI4NCwtMC44MzgyMSAwLjA3MDQsLTMuNDIxNTYgLTAuMDI4NCwtNi42NTg1NyAtMC4yNjk3NCwtOS44MTAzOCAtMS4wNzksLTE0LjI4MjU1IC01LjU2NTM3LC0zMy4wMDg4OCAtMTguNDcwNzksLTUzLjA2OTc3IDEwLjE5MzcyLC0xOC41ODQzNSAxMy43NzE0NiwtMzUuODA1NzcgMTQuMzk2MTQsLTQ5LjA4MDMxIDAuNzI0MDcsLTE1LjY0NTUgMS40MzM5NCwtNDEuMDQ0NiAyLjE0MzgsLTY3LjkzNDQyIDAuNjgxNDgsLTI1LjAyOTk1IDEuMzc3MTUsLTUwLjg4MzM3IDIuMDQ0NDIsLTY1LjgxOSAwLjA5OTksLTEuMzA2MTYgMC4xODQ1NiwtMi42NTQ5MSAwLjE4NDU2LC0zLjk3NTI2IDAsLTAuMjcwMzIgLTAuMDI4NCwtMC41Njc5IC0wLjAyODQsLTAuODM4MjIgMC4wNzA0LC0zLjQzNTc2IC0wLjAyODQsLTYuNjg2OTYgLTAuMjY5NzQsLTkuODEwMzggLTEuNzMyMDgsLTIyLjg4NjE2IC0xMi4xOTU1NSwtNTcuMjU3OTkgLTUyLjE0Njk1LC05MC4xMzkxIEMgODY2LjIxMDczLDEwMy4yNjc5OSA4MzMuMTg3NjUsODUuNjIwNjU4IDc5Mi42Mzk5Niw3MS4wNTQxNTUgNzU0Ljg2MDc2LDU3LjQ2NzI3MSA3MTEuNTQ0NTgsNDYuODQ3NjM3IDY2My44Njk4MSwzOS41MjE4IDYxNS40NDI1OSwzMi4xMTA3NzQgNTY0LjM0NjI1LDI4LjM2MjY2NiA1MTEuOTg2MzUsMjguMzYyNjY2IGMgLTUyLjM1OTksMCAtMTAzLjQ0MjAzLDMuNzQ4MTA4IC0xNTEuODY5MjcsMTEuMTU5MTM0IC00Ny42NDYzNyw3LjMyNTgzNyAtOTAuOTkwOTQsMTcuOTQ1NDcxIC0xMjguNzk4NTQsMzEuNTQ2NTU1IC00MC41MTkyOSwxNC41NTIzMDMgLTczLjU0MjM3LDMyLjE5OTYzNSAtOTguMTE4MDIsNTIuNDU5Mjg1IC0zOS45MjMwMDUsMzIuODgxMSAtNTAuMzg2NDY1LDY3LjIzODczIC01Mi4xMzI3NDUsOTAuMjY2ODcgLTAuMjcwMzEsMy4zMzYzOCAtMC4zNDA3Myw2Ljg5OTkyIC0wLjIyNzE1LDEwLjczMzIyIDAsMC43NjY2NSAwLjAzNDEsMS41MTkxMSAwLjA1NjgsMi4yNTczOCBsIDAuMDQ1NCwwLjkwODYzIGMgMC42NTMwOCwxNC40MTAzMyAxLjM3NzE0LDQwLjg0NTgzIDIuMDcyODEsNjYuMzg2ODkgMC43MjM1LDI2Ljg4OTgzIDEuNDE5NzQsNTIuMjg4OTIgMi4xMTU0MSw2Ny44NjM0NCAwLjYzODMyLDEzLjMzMTMyIDQuMjMwODIsMzAuNTUyNzQgMTQuNDI0NTMsNDkuMTUxMjkgLTEyLjkwNTQxLDIwLjA4OTI4IC0xNy4zOTE3OCwzOC44NDQwMSAtMTguNDg0OTgsNTMuMTk3NTUgLTAuMjU1NTUsMy4zNjQ3NyAtMC4zNDA3Myw2LjkyODMxIC0wLjIyNzE2LDEwLjc2MTYgMCwwLjc1MTg5IDAuMDM0MSwxLjUxOTEyIDAuMDU2OCwyLjI4NTc3IGwgMC4wNDU0LDAuODUxODUgYyAwLjY1MzA4LDE0LjQzODczIDEuMzc3MTQsNDAuODc0MjIgMi4wNzI4MSw2Ni40Mjk0OSAwLjcyMzUsMjYuODg5ODIgMS40MTk3NCw1Mi4yODg5MSAyLjExNTQxLDY3Ljg0OTI0IDAuNjM4MzIsMTMuMzMxMzIgNC4yMzA4MiwzMC41NTI3MyAxNC40NTI5Miw0OS4xMjI4OSAtMTIuOTMzOCwyMC4wODkyOCAtMTcuNDIwMTcsMzguODcyNCAtMTguNTEzMzcsNTMuMjQwMTQgLTAuMjcwMzIsMy4zNTA1OCAtMC4zNDA3Myw2LjkxNDEyIC0wLjIyNzE2LDEwLjc0NzQxIDAsMC43NjY2NiAwLjAzNDEsMS41MzMzMiAwLjA1NjgsMi4yODU3OCBsIDAuMDQ1NCwwLjg1MTg0IGMgMC42NTMwNywxNC40NTI5MiAxLjM3NzE0LDQwLjg0NTgzIDIuMDcyODEsNjYuNDAxMSAwLjcyMzUsMjYuODc1NjEgMS40MTk3NCw1Mi4yODg5MSAyLjExNTQxLDY3Ljg2MzQyIDEuMDkyNjMsMjIuODE1MTggMTAuMjY0NjksNTYuOTAzMDcgNDcuODAyNTQ1LDkwLjA1MzkyIDIzLjYyNDQyLDIwLjg0MTc0IDU1LjgyNDA2LDM5LjA5OTU2IDk1LjcwNDQ3LDU0LjIzMzk0IDM3LjU1MjA0LDE0LjI4MjYgODAuOTY3NiwyNS40NTU5IDEyOS4wMTE1LDMzLjE5MzUgNDguOTUyNTMsNy44Nzk1IDEwMC44ODY1MSwxMS44OTc0IDE1NC4zMzk2MSwxMS44OTc0IDUzLjQ2NzI5LDAgMTA1LjQwMTI4LC00LjAxNzkgMTU0LjM1MzgsLTExLjkxMTYgNDguMDcyMywtNy43Mzc2IDkxLjQ4Nzg2LC0xOC45MTA5IDEyOS4wMzk5LC0zMy4xNzkzIDM5Ljg4MDQxLC0xNS4xMzQzOCA3Mi4wODAwNCwtMzMuMzkyMiA5NS43MDQ0NywtNTQuMjc2NTMgMzcuNTA5NDUsLTMzLjEwODI2IDQ2LjY4MDk1LC02Ny4xOTYxNSA0Ny43NDU3NiwtODkuOTQwMzQgMC43MjQwNiwtMTUuNjQ1NSAxLjQzMzkzLC00MS4wNDQ2IDIuMTQzOCwtNjcuOTIwMjEgMC42ODE0NywtMjUuMDQ0MTYgMS4zNzcxNSwtNTAuODgzMzggMi4wNDQ0MSwtNjUuODE5MDEgMC4xLC0xLjMwNjE2IDAuMTg0NTcsLTIuNjQwNzEgMC4xODQ1NywtMy45NzUyNyAwLC0wLjI4Mzk0IC0wLjAyODQsLTAuNTY3ODkgLTAuMDI4NCwtMC44MzcwNyAwLjA3MDQsLTMuNDM1NzcgLTAuMDI4NCwtNi42NzI3NyAtMC4yNjk3NCwtOS44MTAzOCAtMS4wNzksLTE0LjI4MjU2IC01LjU2NTM3LC0zMy4wMjMwOSAtMTguNDcwNzgsLTUzLjA5ODE3IDEwLjE5MzcxLC0xOC41NTU5NiAxMy43NzE0NSwtMzUuNzQ4OTggMTQuMzk2MTQsLTQ5LjAzNzcxIDAuNzI0MDYsLTE1LjY0NTUxIDEuNDMzOTMsLTQxLjA0NDYgMi4xNDM4LC02Ny45MzQ0MiIgLz4gICAgPGcgICAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMC44MDAwMDAyNCwwLDAsLTAuMjUzMDYxNDcsLTE2My45MzU4NSwyMTEuNDQzOTgpIiAgICAgICBpZD0iZzMzNzYiPiAgICAgIDxwYXRoICAgICAgICAgaWQ9InBhdGgzMzc4IiAgICAgICAgIHN0eWxlPSJmaWxsOiNiY2JlYzA7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlOm5vbmUiICAgICAgICAgZD0ibSAxMDA0LjA1MjIsLTQxLjY4MzE3OCBjIDQ1LjQxNDIsMCA4Mi4yNDU2LDM2LjgzNDAwMDggODIuMjQ1Niw4Mi4yNTA4NzYgMCw0NS40MTc3NTEgLTM2LjgzMTQsODIuMjUwMDAyIC04Mi4yNDU2LDgyLjI1MDAwMiAtNDUuNDE3NzcsMCAtODIuMjQ2NTIsLTM2LjgzMjI1MSAtODIuMjQ2NTIsLTgyLjI1MDAwMiAwLC00NS40MTY4NzUyIDM2LjgyODc1LC04Mi4yNTA4NzYgODIuMjQ2NTIsLTgyLjI1MDg3NiBNIDg1MS4yMDk4MSwyNS42NjkwNzMgYyAwLDg4Ljg3NTQ5NyA1NC4zMTgyNSwxNjguNDYyMDA3IDE1Mi44NDIzOSwxNjguNDYyMDA3IDg0Ljc5MTksMCAxNTMuNTUyLC02OC43NjgwMSAxNTMuNTUyLC0xNTMuNTYzMzgyIDAsLTg0Ljc5NTM3NiAtNjguNzYwMSwtMTUzLjU2MTYyOCAtMTUzLjU1MiwtMTUzLjU2MTYyOCAtMzAuMjY0NTIsMCAtNTguNDg0MTQsOC43NTc4OCAtODIuMjY3NTIsMjMuODgwNTAyIGwgMCwtMjE0LjM2ODg3MiBjIDE1OC4xODA3MiwzNS4xMDUgMjc2LjUwMjYyLDE3Ni4yNTgyNSAyNzYuNTAyNjIsMzQ0Ljk5NDk5OCAwLDE5NS4xMzYzODIgLTE1OC4yNDExLDM1My4zODM2MzIgLTM1My4zNjYxMiwzNTMuMzgzNjMyIC03OS4wMDQ2MiwwIC0xNTEuOTYzODcsLTI1Ljk0MiAtMjEwLjgxODEyLC02OS43NzMzOCBsIDAsOTkuODM5MjUgYyA2Mi41MzAxMiwzNC40NjI3NSAxMzQuMzg3NzUsNTQuMDcyMzggMjEwLjgxODEyLDU0LjA3MjM4IDI0MS41ODIyMiwwIDQzNy40OTgyMiwtMTk1LjkyNTYzIDQzNy40OTgyMiwtNDM3LjUyMTg4MiAwLC0yMzkuNDk0NDk4IC0xOTIuNTIyNywtNDM0LjExMTEyOCAtNDMxLjIwOTU5LC00MzcuNDc4MTI4IGwgMCwyNy43NTg1IDAsMzkzLjg3NjAwMyB6IiAvPiAgICA8L2c+ICAgIDxnICAgICAgIHRyYW5zZm9ybT0ibWF0cml4KDAuNzAwMDAwMjIsMCwwLC0wLjIyMTQyODc5LDM4NC4xMjYxNywxNzkuODg1MjQpIiAgICAgICBpZD0iZzMzODAiPiAgICAgIDxwYXRoICAgICAgICAgaWQ9InBhdGgzMzgyIiAgICAgICAgIHN0eWxlPSJmaWxsOiM1MjY1OGQ7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlOm5vbmUiICAgICAgICAgZD0ibSAwLDAgYyA1MS45MDQsMCA5My45OTYsLTQyLjA5NCA5My45OTYsLTkzLjk5OSAwLC01MS45MDYgLTQyLjA5MiwtOTQgLTkzLjk5NiwtOTQgLTUxLjkwNiwwIC05My45OTYsNDIuMDk0IC05My45OTYsOTQgQyAtOTMuOTk2LC00Mi4wOTQgLTUxLjkwNiwwIDAsMCBtIC0yMjEuMTcsLTk1LjA4MSBjIDAsNjcuOTg3IDE2LjgwOSwxMzIuMDU3IDQ2LjQ5MywxODguMjc2IGwgMCwtMTcwLjE2NiBjIDAsLTEwMS41NzMgNjIuMDc4LC0xOTIuNTI4IDE3NC42NzcsLTE5Mi41MjggOTYuOTA1LDAgMTc1LjQ5LDc4LjU5MSAxNzUuNDksMTc1LjUgQyAxNzUuNDksMi45MDkgOTYuOTA1LDgxLjQ5OSAwLDgxLjQ5OSBjIC0zNC41ODYsMCAtNjYuODM4LC0xMC4wMSAtOTQuMDE1LC0yNy4yOTIgbCAwLDE5NS4zNTYgMCw3MS44NzggQyAtMjI4LjYxMywyMzEuODI4IC0zMTcuMzIzLDc4LjcxMiAtMzE3LjMyMywtOTUuMDgxIGMgMCwtMjY0LjMzOSAyMDUuMjI1LC00ODAuODQgNDY0Ljk5MywtNDk4LjgxNyBsIDAsOTYuNDUgYyAtMjA2LjYxNiwxNy43NTEgLTM2OC44NCwxOTEuMTUyIC0zNjguODQsNDAyLjM2NyIgLz4gICAgPC9nPiAgPC9nPjwvc3ZnPg=='
			);
		}

		// Because the loader does not know the page's given hook name until we've added the page to the menu.
		if ( ! empty( $page_hook ) ) {
			do_action( 'table_pixie_admin_page_hooked', $page_hook );
		}
	}

	/**
	 * The admin page has been hooked.
	 *
	 * @param bool|string $page_hook The admin page's hook name.
	 */
	public function admin_page_hooked( $page_hook ) {
		$this->page_hook = $page_hook;

		add_action( "load-{$this->page_hook}", array( $this, 'add_screen_options' ) );
		add_action( "load-{$this->page_hook}", array( $this, 'add_screen_help' ) );
		add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 2 );

		if ( is_multisite() && is_network_admin() ) {
			add_filter( "manage_{$this->page_hook}-network_columns", array( $this, 'manage_screen_columns' ) );
		} else {
			add_filter( "manage_{$this->page_hook}_columns", array( $this, 'manage_screen_columns' ) );
		}
	}

	/**
	 * Ensure hidden columns correctly set given that WP does not supply columns not currently visible.
	 *
	 * @param integer $meta_id
	 * @param integer $object_id
	 * @param string  $meta_key
	 * @param mixed   $meta_value
	 */
	public function update_columns_hidden( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( "wp-table-pixiecolumnshidden" === substr( $meta_key, -27 ) ) {
			$cols_exist   = array_intersect( array( 'related_id', 'autoload' ), $meta_value );
			$cols_missing = array_diff( array( 'related_id', 'autoload' ), $meta_value );
			if ( is_array( $meta_value ) && ! empty( $cols_exist ) && ! empty( $cols_missing ) ) {
				$hidden = array_keys( array_flip( array_merge( $meta_value, array( 'related_id', 'autoload' ) ) ) );
				update_user_option( $object_id, $meta_key, $hidden, true );
			}
		}
	}

	/**
	 * Add screen options.
	 */
	public function add_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'label'   => _x( 'Records', 'Records to show per page (screen option).', 'wp-table-pixie' ),
				'default' => 20,
				'option'  => 'table_pixie_records_per_page',
			)
		);
	}

	/**
	 * Add screen help.
	 */
	public function add_screen_help() {
		$screen = get_current_screen();

		$help_tabs[] = array(
			'id'      => 'help-columns',
			'title'   => __( 'Columns', 'wp-table-pixie' ),
			'content' => __( '
					<p>
						<dl>
							<dt>Key</dt>
							<dd>
							The name given to each record, used by WordPress and third parties to get and set records along with the Related ID.<br>
							If the Key begins with "_transient_" it is a temporary record that will be removed after a specific timeout period.<br>
							Keys beginning with "_transient_timeout_" specify the UNIX timestamp after which the record and its counterpart may be removed.
							</dd>
							<dt>Value</dt>
							<dd>The actual value for the record, can be any format that can be stored in a string.</dd>
							<dt>Type</dt>
							<dd>
							A column created by the plugin to show the type of data being stored in the Value. This column is not stored in the database.<br>
							When the type is blank the Value is general text or numeric data.<br>
							<strong>"S"</strong> is for Serialized data.<br>
							<strong>"J"</strong> is for JSON data.<br>
							<strong>"O"</strong> is for Object.<br>
							<strong>"b64"</strong> is for Base 64 encoded data. At present the plugin can only determine if data has been Base 64 encoded if the contained data is Serialized, JSON or an Object.<br>
							<strong>"!!!"</strong> is shown when the Serialized value is broken in some way, usually by string length indicators not matching the length of the string it partners.<br>
							This column can not be sorted as it is derived.
							</dd>
							<dt>ID</dt>
							<dd>The unique ID given automatically to each record.</dd>
							<dt>Related ID</dt>
							<dd>The ID for the related record that the metadata is for.</dd>
							<dt>Autoload (options table only)</dt>
							<dd>Can be "yes" or "no". Determines whether the option should be automatically loaded into the object cache on every page load.</dd>
						</dl>
					</p>
					<p>
						The <strong>ID</strong>, <strong>Type</strong>, <strong>Related ID</strong> and <strong>Autoload</strong> columns can be shown and hidden from the Screen Options panel.
					</p>
				',
				'wp-table-pixie'
			),
		);
		$help_tabs[] = array(
			'id'      => 'help-search',
			'title'   => __( 'Search', 'wp-table-pixie' ),
			'content' => __( '
					<p>
						You can search and filter the shown commentmeta, options, postmeta, sitemeta, termmeta and usermeta records by entering text into the Search box on the top right of the table and using the "Search" button.<br>
						When you use the search box, the plugin will show all records that either have the same ID or Related ID if numeric, or where the Key or Value contains the search text.<br>
					</p>
					<p>
						You can also use the "All", "Permanent" and "Transient" links to restrict the records being shown to those types of records when searching the sitemeta table on a Multisite.
					</p>
					<p>
						The number of records to show per page can be changed from the "Screen Options" panel. There you will find a "Records" box where you can change the number of records to show, use the "Apply" button to confirm the change.
					</p>
				',
				'wp-table-pixie'
			),
		);
		$help_tabs[] = array(
			'id'      => 'help-rich-view',
			'title'   => __( 'Rich View', 'wp-table-pixie' ),
			'content' => __( '
					<p>
						The default List View with icon <span class="dashicons list-view"></span> shows the Value fields in their plain text form.
					</p>
					<p>
						The Rich View with icon <span class="dashicons excerpt-view"></span> shows the more complex data in Value fields in an easier to understand manner.<br>
						When the data in the Value field can be converted into an array of values the plugin will show the keys and values, and also expansion controls when there are multiple levels.
					</p>
				',
				'wp-table-pixie'
			),
		);

		$help_tabs = apply_filters( 'table_pixie_set_help_tabs', $help_tabs );

		if ( ! empty( $help_tabs ) && is_array( $help_tabs ) ) {
			foreach ( $help_tabs as $help_tab ) {
				if ( ! empty( $help_tab['id'] ) && ! empty( $help_tab['title'] ) && ! empty( $help_tab['content'] ) ) {
					$screen->add_help_tab( $help_tab );
				}
			}
		}

		$help_sidebar = '<p><strong>Support:</strong></p>';
		$help_sidebar .= '<p>Can\'t find an answer in the Help area?</p>';
		$help_sidebar .= '<p>Having problems with WP Table Pixie?</p>';
		$help_sidebar .= '<p><a href="' . table_pixie_support_url() . '" class="button button-primary regular" target="_blank">Get in touch!</a></p>';

		$help_sidebar = apply_filters( 'table_pixie_set_help_sidebar', $help_sidebar );

		if ( ! empty( $help_sidebar ) ) {
			$screen->set_help_sidebar( $help_sidebar );
		}
	}

	/**
	 * Handles screen_settings filter to add screen settings to Screen Options panel.
	 *
	 * @param string    $screen_settings
	 * @param WP_Screen $screen
	 *
	 * @return string
	 */
	public function screen_settings( $screen_settings, $screen ) {
		// Only add our extra screen settings when on our screen.
		if ( empty( $this->page_hook ) || ! $this->our_screen( $screen, $this->page_hook ) ) {
			return $screen_settings;
		}

		$remember_search = true;
		$options         = get_user_option( 'table_pixie_screen_options' );

		if ( false !== $options && isset( $options['remember_search'] ) && is_bool( $options['remember_search'] ) ) {
			$remember_search = $options['remember_search'];
		}

		$checked         = $remember_search ? ' checked="checked"' : '';
		$screen_settings .= '<label for="remember-search">';
		$screen_settings .= '<input class="remember-search-tog" name="remember_search" type="checkbox" id="remember-search"' . $checked . '>';
		$screen_settings .= __( 'Remember Search & Sort', 'wp-table-pixie' );
		$screen_settings .= '</label>';

		return $screen_settings;
	}

	/**
	 * Handle AJAX requests to save state of Remember Search Screen option.
	 */
	public function ajax_toggle_remember_search() {
		check_ajax_referer( 'table-pixie-nonce', 'nonce' );
		$options = get_user_option( 'table_pixie_screen_options' );

		$options['remember_search'] = ! ( 'false' == $_REQUEST['remember_search'] );

		update_user_option( get_current_user_id(), 'table_pixie_screen_options', $options );
		wp_die();
	}

	/**
	 * Handles the manage_$screen->id_columns filter to supply columns that can be shown or hidden via the screen
	 * options panel.
	 *
	 * @param array $columns
	 *
	 * @return mixed
	 */
	public function manage_screen_columns( $columns ) {
		$columns['type']    = __( 'Type', 'wp-table-pixie' );
		$columns['__tp_id'] = __( 'ID', 'wp-table-pixie' );

		// WordPress's Ajax will only set columns as hidden that are available for current screen.
		$table        = empty( $_REQUEST['table'] ) ? '' : sanitize_key( $_REQUEST['table'] );
		$extra_column = 'options' === $table ? 'autoload' : 'related_id';

		$columns[ $extra_column ] = __( 'Related ID/Autoload', 'wp-table-pixie' );

		return $columns;
	}

	/**
	 * Display the admin page.
	 */
	public function display_admin_page() {
		$table_pixie_list_table = new Table_Pixie_List_Table( $this->page_hook );
		$table_pixie_list_table->prepare_items();

		include plugin_dir_path( __FILE__ ) . 'partials/table-pixie-admin-display.php';
	}

	/**
	 * Let records_per_page option be set.
	 *
	 * @param string $status
	 * @param string $option The option name.
	 * @param string $value  The option value.
	 *
	 * @return string, The status or value.
	 */
	public function set_records_per_page_option( $status, $option, $value ) {
		if ( 'table_pixie_records_per_page' == $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Formats the meta_value column for display.
	 *
	 * @param string $value
	 * @param object $item
	 * @param array  $options
	 *
	 * @return string
	 */
	public function column_meta_value( $value, $item, $options ) {
		global $mode;

		$chars = 100;
		if ( 'list' == $mode && strlen( trim( $value ) ) > $chars ) {
			if ( ! isset( $options['collapsed'] ) || true === $options['collapsed'] ) {
				if ( is_serialized( $value ) ) {
					$boundary = ';';
				} elseif ( Table_Pixie_Data_Format::is_json( $value ) ) {
					$boundary = ',';
				} else {
					$boundary = ' ';
				}

				$truncated = $this->truncate_chars( $value, $chars, $boundary );

				if ( $truncated !== $value ) {
					$value = $truncated . ' &hellip;';
				}
			}
		} elseif ( 'excerpt' === $mode ) {
			$value = Table_Pixie_Data_Format::to_html( $value, 'table-pixie-rich-view' );
		}

		// Whether truncated or not, in list mode we're handling raw data that must be escaped.
		if ( 'list' == $mode ) {
			$value = esc_html( $value );
		}

		return $value;
	}

	/**
	 * Builds row actions for the meta_value column.
	 *
	 * @param array  $actions
	 * @param object $item
	 * @param array  $options
	 *
	 * @return array
	 */
	public function column_meta_value_row_actions( $actions, $item, $options ) {
		global $mode;

		$field_name = empty( $field_name ) ? 'meta_value' : $field_name;

		$chars = 100;
		if ( 'list' == $mode && strlen( trim( $item->meta_value ) ) > $chars ) {
			if ( ! isset( $options['collapsed'] ) || true === $options['collapsed'] ) {
				$value = $this->column_meta_value( $item->{$field_name}, $item, $options );

				if ( $item->{$field_name} !== $value ) {
					$actions = array(
						'expand' => '<a href="#" class="truncate collapsed">' . _x( 'Expand', 'Show more data', 'wp-table-pixie' ) . '</a>',
					);
				}
			} else {
				$actions = array(
					'collapse' => '<a href="#" class="truncate expanded">' . _x( 'Collapse', 'Show less data', 'wp-table-pixie' ) . '</a>',
				);
			}
		} elseif ( 'excerpt' == $mode && Table_Pixie_Data_Format::is_expandable( $item->{$field_name} ) ) {
			$actions = array(
				'expand_all'   => '<a href="#" class="expand-all">' . _x( 'Expand All', 'Show all collapsed array data', 'wp-table-pixie' ) . '</a>',
				'collapse_all' => '<a href="#" class="collapse-all">' . _x( 'Collapse All', 'Collapse and hide all array data', 'wp-table-pixie' ) . '</a>',
			);
		}

		return $actions;
	}

	/**
	 * Formats the related_id column for display.
	 *
	 * @param string $value
	 * @param object $item
	 * @param array  $options
	 *
	 * @return string
	 */
	public function column_related_id( $value, $item, $options ) {
		$blog_id = empty( $_REQUEST['blog_id'] ) ? '' : sanitize_key( $_REQUEST['blog_id'] );
		$table   = empty( $_REQUEST['table'] ) ? '' : sanitize_key( $_REQUEST['table'] );

		$output = $value;

		if ( in_array( $table, array( 'commentmeta', 'postmeta', 'usermeta' ) ) ) {
			if ( is_numeric( $blog_id ) && is_multisite() ) {
				$blog_id = (int) $blog_id;
				switch_to_blog( $blog_id );
			}

			$output = '<a href="';

			switch ( $table ) {
				case 'commentmeta':
					$output .= get_edit_comment_link( $value );
					break;
				case 'postmeta':
					$output .= get_edit_post_link( $value );
					break;
				case 'usermeta':
					$output .= get_edit_user_link( $value );
					break;
			}

			$output .= '">' . $value . '</a>';

			if ( is_numeric( $blog_id ) && is_multisite() ) {
				restore_current_blog();
			}
		} elseif ( 'sitemeta' == $table ) {
			$output = '<a href="' . esc_url( network_admin_url( 'site-info.php?id=' . $value ) ) . '">' . $value . '</a>';
		}

		return $output;
	}

	/**
	 * Handler for table_pixie_column_display filter.
	 *
	 * @param mixed  $value
	 * @param object $item
	 * @param array  $options
	 *
	 * @return string
	 */
	public function column_display( $value, $item, $options = array() ) {
		if ( empty( $item ) || empty( $options['column'] ) ) {
			return $value;
		}

		switch ( $options['column'] ) {
			case 'meta_value':
				$value = $this->column_meta_value( $value, $item, $options );
				break;
			case 'type':
				$value = join( ' / ', $value );

				if ( false !== strpos( $value, '!!!' ) ) {
					$value = Table_Pixie_Data_Format::wrap_with_error( $value, __( 'Broken data', 'wp-table-pixie' ) );
				}
				break;
			case 'related_id':
				$value = $this->column_related_id( $value, $item, $options );
				break;
		}

		// If the value to be displayed is the same as the raw data it must be escaped before display.
		if ( isset( $item->{$options['column']} ) &&
		     ! empty( $item->{$options['column']} ) &&
		     $value === $item->{$options['column']}
		) {
			$value = esc_html( $value );
		}

		return $value;
	}

	/**
	 * Handler for table_pixie_column_row_actions filter.
	 *
	 * @param mixed  $actions
	 * @param object $item
	 * @param array  $options
	 *
	 * @return string
	 */
	public function column_row_actions( $actions, $item, $options = array() ) {
		if ( empty( $item ) || empty( $options['column'] ) || ! isset( $item->{$options['column']} ) || empty( $item->{$options['column']} ) ) {
			return $actions;
		}

		switch ( $options['column'] ) {
			case 'meta_value':
				$actions = $this->column_meta_value_row_actions( $actions, $item, $options );
				break;
		}

		return $actions;
	}

	/**
	 * Handles AJAX request to toggle truncation of a column.
	 */
	public function ajax_toggle_truncate() {
		global $mode;
		$mode = ( ! empty( $_REQUEST['mode'] ) && 'excerpt' == $_REQUEST['mode'] ) ? 'excerpt' : 'list';

		$blog_id   = empty( $_REQUEST['blog_id'] ) ? '' : sanitize_key( $_REQUEST['blog_id'] );
		$table     = empty( $_REQUEST['table'] ) ? 'postmeta' : sanitize_key( $_REQUEST['table'] );
		$id        = sanitize_key( $_REQUEST['__tp_id'] );
		$column    = sanitize_key( $_REQUEST['column'] );
		$collapsed = sanitize_key( $_REQUEST['collapsed'] );

		check_ajax_referer( 'table-pixie-nonce', 'nonce' );

		$item = apply_filters( 'table_pixie_get_item', null, array(
			'blog_id' => $blog_id,
			'table'   => $table,
			'__tp_id' => $id,
		) );

		if ( ! empty( $item ) ) {
			$options['column']    = $column;
			$options['collapsed'] = ! ( 'true' === $collapsed );
			$value                = apply_filters( 'table_pixie_column_display', $item->{$column}, $item, $options );
			$row_actions          = apply_filters( 'table_pixie_column_row_actions', $item->{$column}, $item, $options );
			$row_actions          = apply_filters( 'table_pixie_format_row_actions', $row_actions );
			echo $value . $row_actions;
		}
		wp_die();
	}

	/**
	 * Returns the SELECT fields required for a table.
	 *
	 * @param string $table Table name
	 *
	 * @return string
	 */
	public function get_select_fields( $table ) {
		$query = 'SELECT *';

		if ( 'options' === $table ) {
			$query .= ', option_id AS __tp_id';
			$query .= ', option_name AS meta_key';
			$query .= ', option_value AS meta_value';
		} elseif ( 'usermeta' === $table ) {
			$query .= ', umeta_id AS __tp_id';
		} else {
			$query .= ', meta_id AS __tp_id';
		}

		switch ( $table ) {
			case 'commentmeta':
				$query .= ', comment_id AS related_id';
				break;
			case 'postmeta':
				$query .= ', post_id AS related_id';
				break;
			case 'sitemeta':
				$query .= ', site_id AS related_id';
				break;
			case 'termmeta':
				$query .= ', term_id AS related_id';
				break;
			case 'usermeta':
				$query .= ', user_id AS related_id';
				break;
		}

		return $query;
	}

	/**
	 * Generate query string for given parameters.
	 *
	 * @param bool  $count
	 * @param array $options
	 *
	 * @return string
	 */
	public
	function get_query_string(
		$count = false, $options = array()
	) {
		global $wpdb;

		$blog_id     = empty( $options['blog_id'] ) ? '' : sanitize_key( $options['blog_id'] );
		$table       = empty( $options['table'] ) ? 'postmeta' : sanitize_key( $options['table'] );
		$search      = empty( $options['s'] ) ? '' : sanitize_text_field( $options['s'] );
		$record_type = empty( $options['record_type'] ) ? 'all' : sanitize_key( $options['record_type'] );
		$orderby     = empty( $options['orderby'] ) ? '' : sanitize_key( $options['orderby'] );
		$order       = empty( $options['order'] ) ? 'asc' : sanitize_key( $options['order'] );

		// If a blog in a Multisite has been selected, temporarily switch all queries to that blog.
		if ( is_numeric( $blog_id ) && is_multisite() ) {
			$blog_id = (int) $blog_id;
			switch_to_blog( $blog_id );
		}

		if ( true === $count ) {
			$query = 'SELECT COUNT(*) AS num_rows';
		} else {
			$query = $this->get_select_fields( $table );
		}
		$query .= ' FROM ' . $wpdb->{$table};
		$query .= ' WHERE 1=1';

		$search_values = array();

		if ( ! empty( $search ) ) {
			if ( 'options' === $table ) {
				$query .= " AND ( {$wpdb->{$table}}.option_name LIKE %s";
				$query .= " OR {$wpdb->{$table}}.option_value LIKE %s";
			} else {
				$query .= " AND ( {$wpdb->{$table}}.meta_key LIKE %s";
				$query .= " OR {$wpdb->{$table}}.meta_value LIKE %s";
			}
			$search_values[] = "%{$search}%";
			$search_values[] = "%{$search}%";

			if ( is_numeric( $search ) ) {
				if ( 'options' === $table ) {
					$query .= " OR {$wpdb->{$table}}.option_id = %d";
				} elseif ( 'usermeta' === $table ) {
					$query .= " OR {$wpdb->{$table}}.umeta_id = %d";
				} else {
					$query .= " OR {$wpdb->{$table}}.meta_id = %d";
				}
				$search_values[] = "{$search}";

				switch ( $table ) {
					case 'commentmeta':
						$query           .= " OR {$wpdb->{$table}}.comment_id = %d";
						$search_values[] = "{$search}";
						break;
					case 'postmeta':
						$query           .= " OR {$wpdb->{$table}}.post_id = %d";
						$search_values[] = "{$search}";
						break;
					case 'sitemeta':
						$query           .= " OR {$wpdb->{$table}}.site_id = %d";
						$search_values[] = "{$search}";
						break;
					case 'termmeta':
						$query           .= " OR {$wpdb->{$table}}.term_id = %d";
						$search_values[] = "{$search}";
						break;
					case 'usermeta':
						$query           .= " OR {$wpdb->{$table}}.user_id = %d";
						$search_values[] = "{$search}";
						break;
				}
			}
			$query .= ')';
		}

		if ( ! empty( $record_type ) && 'all' != $record_type ) {
			$key_field = 'meta_key';
			if ( 'options' === $table ) {
				$key_field = 'option_name';
			}
			if ( 'transient' === $record_type ) {
				$query .= " AND (";
				$query .= " {$wpdb->{$table}}.{$key_field} LIKE %s";
				$query .= " OR {$wpdb->{$table}}.{$key_field} LIKE %s";
				$query .= " )";
			} else {
				$query .= " AND {$wpdb->{$table}}.{$key_field} NOT LIKE %s";
				$query .= " AND {$wpdb->{$table}}.{$key_field} NOT LIKE %s";
			}
			$search_values[] = '_transient%';
			$search_values[] = '_site_transient%';
		}

		// Parameters that are going to be used to order the result.
		if ( ! $count && ! empty( $orderby ) && ! empty( $order ) ) {
			$query .= " ORDER BY {$orderby} {$order}";
		}

		if ( ! empty( $search_values ) ) {
			$query = $wpdb->prepare( $query, $search_values );
		}

		// If a blog in a Multisite has been selected, switch all queries back to previous blog.
		if ( is_numeric( $blog_id ) && is_multisite() ) {
			restore_current_blog();
		}

		return $query;
	}

	/**
	 * Returns a single item.
	 *
	 * @param object $item
	 * @param array  $options
	 *
	 * @return object
	 */
	public function get_item( $item, $options ) {
		global $wpdb;

		$blog_id  = empty( $options['blog_id'] ) ? '' : sanitize_key( $options['blog_id'] );
		$table    = empty( $options['table'] ) ? 'postmeta' : sanitize_key( $options['table'] );
		$id       = empty( $options['__tp_id'] ) ? '' : sanitize_key( $options['__tp_id'] );
		$meta_key = empty( $options['meta_key'] ) ? '' : sanitize_key( $options['meta_key'] );

		if ( is_numeric( $blog_id ) && is_multisite() ) {
			$blog_id = (int) $blog_id;
			switch_to_blog( $blog_id );
		}

		if ( ! empty( $id ) ) {
			if ( 'options' === $table ) {
				$where[] = "{$wpdb->{$table}}.option_id = %d";
			} elseif ( 'usermeta' === $table ) {
				$where[] = "{$wpdb->{$table}}.umeta_id = %d";
			} else {
				$where[] = "{$wpdb->{$table}}.meta_id = %d";
			}
			$prep[] = $id;
		}

		if ( ! empty( $meta_key ) ) {
			if ( 'options' === $table ) {
				$where[] = "{$wpdb->{$table}}.option_name = %s";
			} else {
				$where[] = "{$wpdb->{$table}}.meta_key = %s";
			}
			$prep[] = $meta_key;
		}

		if ( ! empty( $where ) && ! empty( $prep ) ) {
			$query = $this->get_select_fields( $table );
			$query .= ' FROM ' . $wpdb->{$table};
			$query .= ' WHERE ' . implode( ' AND ', $where );

			$query = $wpdb->prepare( $query, $prep );
			$item  = $wpdb->get_row( $query );
		}

		if ( is_numeric( $blog_id ) && is_multisite() ) {
			restore_current_blog();
		}

		return $item;
	}

	/**
	 * Run query for given parameters and return count of affected records.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function get_count( $options = array() ) {
		global $wpdb;

		$query = apply_filters( 'table_pixie_get_query_string', true, $options );

		return $wpdb->get_row( $query )->num_rows;
	}

	/**
	 * Truncate a string to given number of characters, or less if boundary string found.
	 *
	 * @param string $text
	 * @param int    $chars
	 * @param string $boundary
	 *
	 * @return string
	 */
	function truncate_chars( $text, $chars = 100, $boundary = ';' ) {
		if ( strlen( $text ) > ceil( $chars * 1.2 ) ) {
			$text = substr( $text, 0, $chars );

			// Step back to a boundary if within 20% of max length.
			$boundary_pos = strrpos( $text, $boundary ) + 1;
			if ( ceil( $chars * 0.8 ) <= $boundary_pos ) {
				$text = substr( $text, 0, $boundary_pos );
			}
		}

		return $text;
	}

	/**
	 * Add extra markup in the toolbars before or after the list.
	 *
	 * @param string $output The current output.
	 * @param string $which  Is the markup for after (bottom) or before (top) the list.
	 *
	 * @return string
	 */
	public function extra_tablenav( $output, $which ) {
		if ( $which == 'top' ) {
			// The html that goes before the table is appended to $output here.
			if ( is_multisite() && is_network_admin() ) {
				$output .= '<div class="alignleft actions table-pixie-extra-tablenav">';

				$current_blog_id = empty( $_REQUEST['blog_id'] ) ? '' : sanitize_key( $_REQUEST['blog_id'] );
				$output          .= '<label for="blog-id-selector-top" class="screen-reader-text">' . __( 'Select Site', 'wp-table-pixie' ) . '</label>';
				$output          .= '<select name="blog_id" id="blog-id-selector-top" autocomplete="off">';
				$output          .= '<option value="" disabled="disabled">&mdash; ' . _x( 'Site', 'Site to view records for', 'wp-table-pixie' ) . ' &mdash;</option>';

				foreach ( $this->_get_sites( array( 'limit' => 0 ) ) as $blog ) {
					$blog_id     = empty( $blog['blog_id'] ) ? '' : $blog['blog_id'];
					$description = untrailingslashit( trim( $blog['domain'] ) . trim( $blog['path'] ) );

					$selected = '';
					if ( $current_blog_id == $blog_id ) {
						$selected = ' selected="selected"';
					}
					$output .= sprintf(
						'<option value="%1$s"' . $selected . '>%2$s</option>',
						esc_attr( $blog_id ),
						esc_html( $description )
					);
				}
				$output .= '</select>';
				$output .= '</div>';
			}
			$output .= '<div class="alignleft actions table-pixie-extra-tablenav">';

			$current_table = empty( $_REQUEST['table'] ) ? 'postmeta' : sanitize_key( $_REQUEST['table'] );
			$output        .= '<label for="table-selector-top" class="screen-reader-text">' . __( 'Select Table', 'wp-table-pixie' ) . '</label>';
			$output        .= '<select name="table" id="table-selector-top" autocomplete="off">';
			$output        .= '<option value="" disabled="disabled">&mdash; ' . _x( 'Table', 'Table to view records for', 'wp-table-pixie' ) . ' &mdash;</option>';

			$tables = array(
				'commentmeta',
				'options',
				'postmeta',
				'termmeta',
			);

			if ( is_multisite() && is_network_admin() ) {
				$tables[] = 'usermeta';
				$tables[] = 'sitemeta';
			} elseif ( ! is_multisite() ) {
				$tables[] = 'usermeta';
			}

			foreach ( $tables as $table ) {
				$selected = '';
				if ( $current_table == $table ) {
					$selected = ' selected="selected"';
				}
				$output .= sprintf(
					'<option value="%1$s"' . $selected . '>%2$s</option>',
					esc_attr( $table ),
					esc_html( $table )
				);
			}
			$output .= '</select>';
			$output .= '<input type="submit" name="" id="apply-table-top" class="button action" value="' . __( 'Switch Table', 'wp-table-pixie' ) . '">';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Is the given screen ours?
	 *
	 * @param WP_Screen $screen
	 * @param string    $page_hook
	 *
	 * @return bool
	 */
	public static function our_screen( $screen, $page_hook ) {
		if ( ! empty( $screen->id ) && ! empty( $page_hook ) && ( $screen->id === $page_hook || $screen->id === $page_hook . '-network' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return an array of sites for a network or networks.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function _get_sites( $args ) {
		global $wp_version;

		$results = array();

		if ( version_compare( $wp_version, '4.6-dev', '<' ) ) {
			$results = wp_get_sites( $args );
		} else {
			$_sites = get_sites( $args );

			foreach ( $_sites as $_site ) {
				$_site     = get_site( $_site );
				$results[] = $_site->to_array();
			}
		}

		return $results;
	}
}
