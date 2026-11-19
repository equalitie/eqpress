#!/usr/bin/env php
<?php
// 2014-12-30 RAMNIC - RamJett
// Generate an htpasswd line for nginx auth_basic, e.g. for the press admin
// pages (/webstats/, /perf/):
//
//   htpasswd.php --user=alice >> /etc/nginx/restricted_pages.htpasswd
//
// Options:
//   --user=<name>        user name (prompted if not given)
//   --password=<pass>    password (prompted if not given; prompting is safer,
//                        a password on the command line is visible in ps)
//   --method=<1|2|3>     1 SHA-512 crypt ($6$), recommended
//                        2 Apache MD5 ($apr1$)
//                        3 plain text ({PLAIN}), insecure
//   -h, --help           this help
$options = getopt('h', ['user:', 'password:', 'method:', 'help']);
if (isset($options['h']) || isset($options['help'])) {
  echo "Usage: htpasswd.php [--user=<name>] [--password=<pass>] [--method=1|2|3]\n";
  echo "  1 SHA-512 crypt (recommended), 2 Apache MD5 (apr1), 3 plain text (insecure)\n";
  echo "Example: htpasswd.php --user=alice >> /etc/nginx/restricted_pages.htpasswd\n";
  exit(0);
}
$u = $options['user'] ?? '';
$p = $options['password'] ?? '';
$m = $options['method'] ?? '';

$user = get_username($u);
if ($user === '' || preg_match('/[:\s]/', $user)) {
  fwrite(STDERR, "Invalid user name (empty, or contains ':' or spaces)\n");
  exit(1);
}
$pass = crypt_passwd($p, $m);
print $user . ":" . $pass . "\n";
exit(0);

/////////////////////////////////////////////////////////////////////////

// get username for password file.
function get_username($u)
{
  if ($u == '') {
    fwrite(STDERR, "Type username: ");
    $line = fgets(STDIN);
  } else {
    $line = $u;
  }
  return trim((string) $line);
}

// get password and return hash
function crypt_passwd($p, $m)
{
  if ($p == '') {
    $line = "0";
    $line2 = "1";
    while (($line != $line2) || (trim($line) == '')) {
      fwrite(STDERR, "Type password: ");
      $line = (string) fgets(STDIN);
      fwrite(STDERR, "Verify password: ");
      $line2 = (string) fgets(STDIN);
      if ((trim($line) != trim($line2)) || (trim($line) == '')) {
        fwrite(STDERR, "Passwords do not match or is blank\nTry again\n");
      }
    }
  } else {
    $line = $p;
  }
  $clearpass = trim($line);

  $choice = trim((string) $m);
  while (!in_array($choice, ['1', '2', '3'], true)) {
    fwrite(STDERR, "1 - SHA-512 crypt (recommended)\n");
    fwrite(STDERR, "2 - Apache MD5 (apr1)\n");
    fwrite(STDERR, "3 - Plain, very insecure\n");
    fwrite(STDERR, "Type the number for password algorithm: ");
    $choice = trim((string) fgets(STDIN));
  }
  switch ($choice) {
    case '1':
      return sha512_crypt($clearpass);
    case '2':
      return apr1_md5($clearpass);
    case '3':
    default:
      return '{PLAIN}' . $clearpass;
  }
}

function random_salt($len)
{
  $chars = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  $salt = '';
  for ($i = 0; $i < $len; $i++) {
    $salt .= $chars[random_int(0, strlen($chars) - 1)];
  }
  return $salt;
}

// SHA-512 crypt ($6$), understood by nginx through the system crypt()
function sha512_crypt($input)
{
  return crypt($input, '$6$' . random_salt(16) . '$');
}

// Apache MD5 ($apr1$), implemented by nginx itself
function apr1_md5($input, $salt = null)
{
  $salt = $salt ?? random_salt(8);
  $len = strlen($input);
  $text = $input . '$apr1$' . $salt;
  $bin = pack("H32", md5($input . $salt . $input));
  for ($i = $len; $i > 0; $i -= 16) {
    $text .= substr($bin, 0, min(16, $i));
  }
  for ($i = $len; $i > 0; $i >>= 1) {
    $text .= ($i & 1) ? chr(0) : $input[0];
  }
  $bin = pack("H32", md5($text));
  for ($i = 0; $i < 1000; $i++) {
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
    $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
  }
  $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
  $tmp = strtr(
    strrev(substr(base64_encode($tmp), 2)),
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
  );
  return '$apr1$' . $salt . '$' . $tmp;
}
