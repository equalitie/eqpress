#!/usr/bin/perl -w

use strict;

if(!$ARGV[0] || !$ARGV[1]) { die "usage: prtran len a|n|p where:\na specifies only using alphanumeric chars\nn specifies only using numbers\np specifies using all printable ascii chars\nlen is the length of random string)\n"; }

open R, "/dev/urandom" || die "urandom: $!\n";
#print"\n";
for(my $x=0; $x < $ARGV[0]; $x++) {
    my $c = getc(R);
    if ($ARGV[1] =~ /[aA]/) {
        if ($c =~ /[0-9A-Za-z]/) { print $c; }
        else { $x--; }
    }
    elsif ($ARGV[1] =~ /[nN]/) {
        if ($c =~ /[0-9]/) { print $c; }
        else { $x--; }
    }
    else {
        if ($c =~ /[(-_a-~]/) { print $c; }
        else { $x--; }
    }
}
