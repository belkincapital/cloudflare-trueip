<?php
/*
Plugin Name: CloudFlare TrueIP
Version: 1.0
Description: Integrates your site with CloudFlare so you are able to achieve the true IP address of your site visitors, which by default a Cloudflare IP is shown instead. CloudFlare has released mod_cloudflare for Apache, which logs & displays the actual visitor IP address rather than the CloudFlare address. We aim to provide the same support for WordPress with our CloudFlare TrueIP plugin.
Author:	Jason Jersey
Author URI: https://twitter.com/degersey
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html
    
Copyright 2015 Belkin Capital Ltd (contact: https://belkincapital.com/contact/)
    
This plugin is opensource; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published
by the Free Software Foundation; either version 2 of the License,
or (at your option) any later version (if applicable).
    
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Plugin adapted from the Akismet WP plugin.
*/


/* No direct linking */
if (!defined( 'ABSPATH' ) ) die('Access Forbidden');	

/* Requirements */
require_once("ip_in_range.php");

/* Get True IP */
function cloudflare_trueip() {
    global $is_cf;
    
    $is_cf = (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))? TRUE: FALSE;    

    /* only run this logic if the REMOTE_ADDR is populated, to avoid causing notices in CLI mode */
    if (isset($_SERVER["REMOTE_ADDR"])) {
		if (strpos($_SERVER["REMOTE_ADDR"], ":") === FALSE) {
	
			$cf_ip_ranges = array("199.27.128.0/21","173.245.48.0/20","103.21.244.0/22","103.22.200.0/22","103.31.4.0/22","141.101.64.0/18","108.162.192.0/18","190.93.240.0/20","188.114.96.0/20","197.234.240.0/22","198.41.128.0/17","162.158.0.0/15","104.16.0.0/12","172.64.0.0/13");
			/* IPV4: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range */
			foreach ($cf_ip_ranges as $range) {
				if (ipv4_in_range($_SERVER["REMOTE_ADDR"], $range)) {
					if ($_SERVER["HTTP_CF_CONNECTING_IP"]) {
						$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
					}
					break;
				}
			}        
		} else {
			$cf_ip_ranges = array("2400:cb00::/32","2606:4700::/32","2803:f800::/32","2405:b500::/32","2405:8100::/32");
			$ipv6 = get_ipv6_full($_SERVER["REMOTE_ADDR"]);
			foreach ($cf_ip_ranges as $range) {
				if (ipv6_in_range($ipv6, $range)) {
					if ($_SERVER["HTTP_CF_CONNECTING_IP"]) {
						$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
					}
					break;
				}
			}
		}
	}
}
add_action('init', 'cloudflare_trueip',1);
