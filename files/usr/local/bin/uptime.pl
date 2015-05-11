#!/usr/bin/perl

$uptime = `uptime`;

$uptime =~ /up (.*?) day/;
$up = int($1);

print "$up\n";
print "$up\n";

