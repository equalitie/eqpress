#!/usr/bin/perl

use DBI;

$dbh = DBI->connect('DBI:mysql:mysql', 'root', 'YbaTALhM8q793u4ZbiNBg4X') || die "Could not connect to database: $DBI::errstr";
$sth = $dbh->prepare('SELECT User FROM user');
$sth->execute();

while (@data = $sth->fetchrow_array()) {
            my $firstname = $data[0];
            print "$firstname\n";
          }
$dbh->disconnect();
