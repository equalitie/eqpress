#!/usr/bin/env php
<?php
// 2014-12-30 RAMNIC - RamJett
// PHP to generate a htpasswd
//
// nginx
// auth_basic "Password Needed:";
// auth_basic_user_file /path/to/passwd_file;

// -h or --help for help
// --user=user
// --password=password
// --method=[1,2,or 3] password method 1 crypt, 2 ngx1_md5, 3 plain

$options = getopt('h',['user:','password:','method:','help']);

foreach (array_keys($options) as $opt) switch ($opt) {
  case 'h':
  case 'help':
    print "Help\n";
    exit;
  case 'user':
    $u = $options['user'];
    break;
  case 'password':
    $p = $options['password'];
    break;
  case 'method':
    $m = $options['method'];
    break;
}

if (!isset($u))
 $u="";
if (!isset($p))
 $p="";
if (!isset($m))
 $m="";

$user = get_username($u);
$pass = crypt_passwd($p,$m);
print $user.":".$pass."\n";
exit;
/////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
//  get username for password file.
function get_username($u) {
  if ($u == '') {
    print "Type username: ";
    $line = fgets(STDIN);
  }
  else {
    $line = $u;
  }

  return trim($line);
}
// get password and return crypted hash
function crypt_passwd($p,$m) {
  if ($p == '') {
    $line = "0";
    $line2 = "1";
    while ( ($line != $line2) || (trim($line) == '') ) {
      print "Type password: ";
      $line = fgets(STDIN);
      print "Verfiy password: ";
      $line2 = fgets(STDIN);
      if ((trim($line) != trim($line2)) || (trim($line) == '')) {
        print "Passwords do not match or is blank\n";
        print "Try again\n";
      } 
    }
  }
  else
    $line = $p;

  $clearpass = trim($line);
  $line = '';
  while ($line == '') {
    if ($m == '') {
      print "1 - crypt algorithm\n";
      print "2 - MD5-based algorithm, Apache type\n";
      print "3 - Plain, very insecure\n";
      print "Type the number for password algorithm: ";
      $line = fgets(STDIN);
      }
      else {
        $line = $m;
        // Just in case $line var is not valid
        $m = '';
      }

      if (!( trim($line) == 1 || trim($line) == 2 || trim($line) == 3 ) ) {
        $line = '';
      } 
    }
    switch(trim($line)) {
    // 
      case "1":
        // unix crypt
        $pass = crypt_hash($clearpass);
        break;
      case "2":
        // md5 apache variant
        $pass = ngx1_md5($clearpass);
        break;
      case "3":
        // plain text
        $pass = $clearpass; 
        break;
    }
  return $pass;
}
// sub-function return crypt 
function crypt_hash($input) {
  return crypt($input, base64_encode($input));
}
// sub-function return apache md5
function ngx1_md5($input) {
  $salt = substr(str_shuffle("./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 8);
  $len = strlen($input);
  $text = $input.'$ngx1$'.$salt;
  $bin = pack("H32", md5($input.$salt.$input));
  for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
  for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $input{0}; }
  $bin = pack("H32", md5($text));
  for($i = 0; $i < 1000; $i++) {
    $new = ($i & 1) ? $input : $bin;
    if ($i % 3) $new .= $salt;
    if ($i % 7) $new .= $input;
    $new .= ($i & 1) ? $bin : $input;
    $bin = pack("H32", md5($new));
  }
  $tmp = '';
  for ($i = 0; $i < 5; $i++) {
    $k = $i + 6;
    $j = $i + 12;
    if ($j == 16) $j = 5;
    $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
  }
  $tmp = chr(0).chr(0).$bin[11].$tmp;
  $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
  "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
  "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");

  return "$"."ngx1"."$".$salt."$".$tmp;
}
?>
