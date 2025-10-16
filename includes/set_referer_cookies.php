<?php
function set_brr_referer_cookies() {
	$referer  = false;
	$medium   = false;
	$campaign = false;

	$uri = $_SERVER['REQUEST_URI'];

	$protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

	$host = $_SERVER['HTTP_HOST'];

	$base_url = $protocol . "://" . $host;

	global $current_slug;

	$current_slug = $uri;

	$other_url_params = [
		'ad_group'         => @$_GET['adgroup_id'],
		'matchtype'        => @$_GET['matchtype'],
		'keyword'          => @$_GET['keyword'],
		'campaign_content' => @$_GET['campaign_content'],
		'campaign_term'    => @$_GET['campaign_term'],
		'campaign_id'      => @$_GET['campaign_id']
	];


	if ( $_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' ) {

		// Code is running on localhost
		$rootUri     = explode( '/', $uri );
		$compareSlug = $rootUri[1];
		$base_url    .= '/' . $compareSlug;
		$uriCounter  = 0;
		foreach ( $rootUri as $rUri ) {
			if ( $rUri === $compareSlug ) {
				for ( $i = 0; $i <= $uriCounter; $i ++ ) {
					unset( $rootUri[ $i ] );
				}
			}
			$uriCounter ++;
		}
		$newURI = '';
		foreach ( $rootUri as $rUri ) {
			$newURI .= '/' . $rUri;
		}
		$uri          = $newURI;
		$current_slug = $uri;
	}

	$current_site_url = $base_url . $current_slug;
	$g_redirect       = false;
	$redirect_utm     = false;
	$set_referer      = false;

	if ( ! isset( $_COOKIE['brr_referer'] ) ) {
		// Set a cookie named "brr_referer" with the value $referer that expires in 24 hours
		if ( isset( $_GET['utm_source'] ) ) {
			$referer  = sanitize_text_field( $_GET['utm_source'] );
			$medium   = ( isset( $_GET['utm_medium'] ) ) ? sanitize_text_field( $_GET['utm_medium'] ) : false;
			$campaign = ( isset( $_GET['utm_campaign'] ) ) ? sanitize_text_field( $_GET['utm_campaign'] ) : false;
		} elseif ( isset( $_GET['source1'] ) ) {
			$referer  = sanitize_text_field( $_GET['source1'] );
			$medium   = ( isset( $_GET['medium1'] ) ) ? sanitize_text_field( $_GET['medium1'] ) : ( ( isset( $_GET['utm_medium'] ) ) ? sanitize_text_field( $_GET['utm_medium'] ) : false );
			$campaign = false;
		} else {
			if ( ( isset( $_SERVER['HTTP_REFERER'] ) ) && strpos( $_SERVER['HTTP_REFERER'], 'baransys.com' ) === false ) {
				$referer = ( strpos( $_SERVER['HTTP_REFERER'], 'www.' ) !== false ) ? explode( 'www.', $_SERVER['HTTP_REFERER'] )[1] : $_SERVER['HTTP_REFERER'];
				set_secure_cookie( "http_g_set_already", $referer, time() + 86400, "/" );
				$g_redirect = true;
			}
		}
		delete_stored_cookies( $other_url_params );
		set_secure_cookie( "brr_referer", $referer, time() + 86400, "/" );
		set_secure_cookie( "brr_medium", $medium, time() + 86400, "/" );
		set_secure_cookie( "brr_campaign", $campaign, time() + 86400, "/" );
		set_secure_cookie( "utm_campaign_set_already", 'true', time() + 86400, "/" );
		$redirect_utm = true;
		$set_referer  = true;
	} else {
		if ( isset( $_GET['utm_source'] ) ) {
			$referer  = sanitize_text_field( $_GET['utm_source'] );
			$medium   = ( isset( $_GET['utm_medium'] ) ) ? sanitize_text_field( $_GET['utm_medium'] ) : false;
			$campaign = ( isset( $_GET['utm_campaign'] ) ) ? sanitize_text_field( $_GET['utm_campaign'] ) : false;
			if ( isset( $_COOKIE['utm_campaign_set_already'] ) && trim( $_COOKIE['utm_campaign_set_already'] ) === 'true' ) {
				if ( $referer !== trim( $_COOKIE['brr_referer'] ) ) {
					$set_referer = true;
				}
			} else {
				$set_referer = true;
			}
			if ( $set_referer ) {
				delete_stored_cookies( $other_url_params );
				set_secure_cookie( "brr_referer", $referer, time() + 86400, "/" );
				set_secure_cookie( "brr_medium", $medium, time() + 86400, "/" );
				set_secure_cookie( "brr_campaign", $campaign, time() + 86400, "/" );
				set_secure_cookie( "utm_campaign_set_already", 'true', time() + 86400, "/" );
				$redirect_utm = true;
			}
		} elseif ( isset( $_GET['source1'] ) ) {
			$referer  = sanitize_text_field( $_GET['source1'] );
			$medium   = ( isset( $_GET['medium1'] ) ) ? sanitize_text_field( $_GET['medium1'] ) : ( ( isset( $_GET['utm_medium'] ) ) ? sanitize_text_field( $_GET['utm_medium'] ) : false );
			$campaign = false;
			if ( isset( $_COOKIE['utm_campaign_set_already'] ) && trim( $_COOKIE['utm_campaign_set_already'] ) === 'true' ) {
				if ( $referer !== trim( $_COOKIE['brr_referer'] ) ) {
					$set_referer = true;
				}
			} else {
				$set_referer = true;
			}
			if ( $set_referer ) {
				delete_stored_cookies( $other_url_params );
				set_secure_cookie( "brr_referer", $referer, time() + 86400, "/" );
				set_secure_cookie( "brr_medium", $medium, time() + 86400, "/" );
				set_secure_cookie( "brr_campaign", $campaign, time() + 86400, "/" );
				set_secure_cookie( "utm_campaign_set_already", 'true', time() + 86400, "/" );
				$redirect_utm = true;
			}
		} elseif ( ( isset( $_SERVER['HTTP_REFERER'] ) ) && strpos( $_SERVER['HTTP_REFERER'], 'baransys.com' ) === false ) {
			$referer = ( strpos( $_SERVER['HTTP_REFERER'], 'www.' ) !== false ) ? explode( 'www.', $_SERVER['HTTP_REFERER'] )[1] : $_SERVER['HTTP_REFERER'];
			if ( $referer !== $_COOKIE['brr_referer'] ) {
				delete_stored_cookies( $other_url_params );
				set_secure_cookie( "brr_referer", $referer, time() + 86400, "/" );
				set_secure_cookie( "brr_medium", '', time() + 86400, "/" );
				set_secure_cookie( "brr_campaign", '', time() + 86400, "/" );
				set_secure_cookie( "http_g_set_already", $referer, time() + 86400, "/" );
				$g_redirect = true;
			}
		}
	}

	$url_components = parse_url( $current_site_url );

	$is_existed = false;

	if ( isset( $url_components['query'] ) ) {
		// Parse the query string into an associative array
		parse_str( $url_components['query'], $query_params );

		// Check if any of the specified parameters exist and remove them
		$params_to_remove = [ 'utm_source', 'utm_campaign', 'utm_medium', 'source1', 'medium1' ];
		foreach ( $params_to_remove as $param ) {
			if ( isset( $query_params[ $param ] ) ) {
				$is_existed = true;
				//unset($query_params[$param]);
			}
		}

		// Rebuild the query string
		$new_query_string = http_build_query( $query_params );

		// Reconstruct the URL with the modified query string
		$current_site_url = $url_components['scheme'] . '://' . $url_components['host'] . $url_components['path'];
		if ( $new_query_string !== '' ) {
			$current_site_url .= '?' . $new_query_string;
		}
	}

	foreach ( $other_url_params as $key => $url_param ) {
		if ( ! empty( $url_param ) ) {
			$cookieParam = sanitize_text_field( $url_param );
			if ( ! isset( $_COOKIE[ $key ] ) || $_COOKIE[ $key ] !== $cookieParam ) {
				set_secure_cookie( $key, $cookieParam, time() + 86400, "/" );
				if ( ! $is_existed ) {
//					set_secure_cookie( "brr_referer", $referer, time() - 86400, "/" );
//					set_secure_cookie( "brr_medium", $medium, time() - 86400, "/" );
//					set_secure_cookie( "brr_campaign", $campaign, time() - 86400, "/" );
					set_secure_cookie( "http_g_set_already", $referer, time() - 86400, "/" );
					set_secure_cookie( "utm_campaign_set_already", 'true', time() - 86400, "/" );
				}
				$g_redirect = true;
			}
		}
	}

	if ( $is_existed ) {
		if ( $redirect_utm ) {
			wp_safe_redirect( $current_site_url );
			exit();
		}
	} else {
		if ( $g_redirect ) {
			wp_safe_redirect( $current_site_url );
			exit();
		}
	}
}

//add_action( 'init', 'set_brr_referer_cookies', 1 );

/**
 * Cross-version helper for setting secure cookies.
 * Accepts either expiry as a timestamp or as seconds from now.
 */
function set_secure_cookie( $name, $value, $expiry = 0, $path = '/' ) {
	$newValue = $value === false ? '' : $value;
	// If expiry passed as timestamp or as seconds offset, normalize to timestamp.
	$expires_at = ( $expiry > 0 && $expiry > time() ) ? $expiry : ( is_int( $expiry ) && $expiry > 0 ? time() + $expiry : $expiry );

	if ( ! headers_sent() ) {
		// Prefer array options when available (PHP 7.3+), otherwise fall back to classic setcookie signature.
		if ( function_exists( 'version_compare' ) && version_compare( PHP_VERSION, '7.3', '>=' ) ) {
			$options = [
				'expires'  => $expires_at,
				'path'     => $path,
				'domain'   => $_SERVER['HTTP_HOST'],
				'secure'   => isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on',
				'httponly' => false, // allow JS to read these cookies where needed
				'samesite' => 'Lax',
			];
			setcookie( $name, $newValue, $options );
		} else {
			// Build cookie header manually for older PHP versions.
			$cookie = rawurlencode( $name ) . '=' . rawurlencode( $newValue );
			if ( $expires_at ) {
				$cookie .= '; expires=' . gmdate( 'D, d-M-Y H:i:s T', $expires_at );
			}
			$cookie .= '; path=' . $path;
			$cookie .= '; domain=' . $_SERVER['HTTP_HOST'];
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) {
				$cookie .= '; Secure';
			}
			$cookie .= '; SameSite=Lax';
			header( 'Set-Cookie: ' . $cookie, false );
		}
	}
}

function init_cookies() {
    // Run on front-end (not admin) and when not doing an AJAX request.
    if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
        if ( ! function_exists( 'wp_doing_ajax' ) || ! wp_doing_ajax() ) {
            set_brr_referer_cookies();
        }
    }
}

	add_action( 'init', 'init_cookies' );

function delete_stored_cookies( $other_url_params ) {
	setcookie( "brr_referer", '', time() - 86400, "/" );
	setcookie( "brr_medium", '', time() - 86400, "/" );
	setcookie( "brr_campaign", '', time() - 86400, "/" );
	setcookie( "http_g_set_already", '', time() - 86400, "/" );
	setcookie( "utm_campaign_set_already", 'false', time() - 86400, "/" );
	foreach ( $other_url_params as $key => $url_param ) {
		if ( isset( $_COOKIE[ $key ] ) ) {
			setcookie( $key, '', time() - 86400, "/" );
		}
	}
}