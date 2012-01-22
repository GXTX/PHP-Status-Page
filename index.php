<?php
/*  PHP System Status
 *  ------------------------------------------
 *  Author: wutno (#/g/tv - Rizon)
 *  Last update: 1/22/2012 3:01PM -5GMT (vnstat)
 *
 *
 *  GNU License Agreement
 *  ---------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 *  http://www.gnu.org/licenses/gpl-2.0.txt
 */

#Since we all enjoy Open Source
if(isset($_GET['dat']) && $_GET['dat'] == "sauce"){
	$lines = implode(range(1, count(file(__FILE__))), '<br />');
	$content = highlight_file(__FILE__, TRUE);
	die('<html><head><title>Page Source For: '.__FILE__.'</title><style type="text/css">body {margin: 0px;margin-left: 5px;}.num {border-right: 1px solid;color: gray;float: left;font-family: monospace;font-size: 13px;margin-right: 6pt;padding-right: 6pt;text-align: right;}code {white-space: nowrap;}td {vertical-align: top;}</style></head><body><table><tr><td class="num"  style="border-left:thin; border-color:#000;">'.$lines.'</td><td class="content">'.$content.'</td></tr></table></body></html>');
}

function kb2bytes($kb){
	return round($kb * 1024, 2);
}
function format_bytes($bytes){
	if ($bytes < 1024){ return $bytes; }
	else if ($bytes < 1048576){ return round($bytes / 1024, 2).'KB'; }
	else if ($bytes < 1073741824){ return round($bytes / 1048576, 2).'MB'; }
	else if ($bytes < 1099511627776){ return round($bytes / 1073741824, 2).'GB'; }
	else{ return round($bytes / 1099511627776, 2).'TB'; }
}
function numbers_only($string){
	return preg_replace('/[^0-9]/', '', $string);
}
function calculate_percentage($used, $total){
	return @round(100 - $used / $total * 100, 2);
}

$uptime = exec('uptime');
preg_match('/ (.+) up (.+) user(.+): (.+)/', $uptime, $update_out);
$users_out = substr($update_out[2], strrpos($update_out[2], ' ')+1);
$uptime_out = substr($update_out[2], 0, strrpos($update_out[2], ' ')-2);
$load_out = str_replace(', ',', <small>',$update_out[4]).str_repeat('</small>',2);

#HDD info
$harddrive = explode(' ', preg_replace('/\s\s+/', ' ', exec('df /dev/xvda1')));
$harddrive_out = format_bytes(kb2bytes($harddrive[2])).'<small> / '.format_bytes(kb2bytes($harddrive[1])).' <small>('.calculate_percentage(kb2bytes($harddrive[2]), kb2bytes($harddrive[1])).'% Free)</small></small>';

#bandwidth info
$vnstat = explode(';', shell_exec('vnstat --oneline'));
$vnstat[8] = '&#8595; '.$vnstat[8];
$vnstat[9] = ' <small>&#8593; '.$vnstat[9];
$vnstat[10] = ' <small>&#8597; '.$vnstat[10];
$vnstat[11] = ' @ ~'.$vnstat[11];

#ram and swap
$memory = array( 'Total RAM'  => 'MemTotal',
				 'Free RAM'   => 'MemFree',
				 'Cached RAM' => 'Cached',
				 'Total Swap' => 'SwapTotal',
				 'Free Swap'  => 'SwapFree' );
foreach ($memory as $key => $value){
	$memory[$key] = kb2bytes(numbers_only(exec('grep -E "^'.$value.'" /proc/meminfo')));
}
$memory['Used Swap'] = $memory['Total Swap'] - $memory['Free Swap'];
$memory['Used RAM'] = $memory['Total RAM'] - $memory['Free RAM'] - $memory['Cached RAM'];
$memory['RAM Percent Free'] = calculate_percentage($memory['Used RAM'],$memory['Total RAM']);
$memory['Swap Percent Free'] = calculate_percentage($memory['Used Swap'],$memory['Total Swap']);
$memory_out = format_bytes($memory['Used RAM']).'<small> / '.format_bytes($memory['Total RAM']).' <small> *'.format_bytes($memory['Cached RAM']).' Cached ('.$memory['RAM Percent Free'].'% Free)</small></small>';
$swap_out = 'swap<span>'.format_bytes($memory['Used Swap']).'<small> / '.format_bytes($memory['Total Swap']).' <small>('.$memory['Swap Percent Free'].'% Free)</small></small></span>';
?>
<!DOCTYPE html>
<html>
	<head>
		<title>#/g/tv - Shell Server</title>
		<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp" /> <!-- I fucking hate robots... -->
		<meta name="description" content="Status page for #/g/tv shell server." />
		<meta charset="UTF-8" />
		<style type="text/css">
			span { color: #fff;display: block;font-size: 1.3em;margin-bottom: .5em;padding: 0 .5em; }
			html { background-color: #000;color: #777;font-family: sans-serif; font-size: 1.8em;padding: 1em 2em; }
			div { float: right;text-align: right; }
			a { color: #68c;display: block;font-size: 1.7em;text-decoration: none; }
			small { color: #bbb; }
			small>small { color: #777; }
		</style>
	</head>
	<body>
		<div id="links">bros <a href="#">xyzzy</a><a href="index.php?dat=sauce">wutno</a><a href="http://noodlebox.dyndns.info/">noodle</a><a href="/~shader/">shader</a><a href="/~sahaquiel_/">sahaquiel_</a><br/>scripts <a href="http://archive.installgentoo.net/">archive</a><a href="http://youshould.installgentoo.net/">gentoo</a></div>
		server time<span><?=date("Y-F-j H:i:s", time());?></span> <!-- Server time is acutally PHP time since PHP wants to bitch -->
		uptime<span><?=$uptime_out;?></span> <!-- Server uptime --> 
		users logged in<span><?=$users_out;?></span> <!-- Users logged in -->
		load<span><?=$load_out;?></span> <!-- CPU load averages -->
		memory<span><?=$memory_out;?> </span> <!-- RAM usage -->
		<?=$swap_out;?> <!-- SWAP usage -->
		disk<span><?=$harddrive_out;?></span> <!-- Disk information -->
		bandwidth used <small></small><span><?=$vnstat[8].$vnstat[9].$vnstat[10].$vnstat[11];?></small></small></span> <!-- Monthly bandwidth usage -->
		<small><small>POWERED BY</small> #/g/tv - Rizon</small>
	</body>
</html>