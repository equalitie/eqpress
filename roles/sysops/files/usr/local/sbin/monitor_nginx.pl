#!/usr/bin/perl
use LWP::UserAgent;

# define your nginx stats URL
my $URL = "http://localhost/nginx_status";

my $ua = LWP::UserAgent->new(timeout => 30);
my $response = $ua->request(HTTP::Request->new('GET', $URL));

my $requests = 0;
my $total =  0;
my $reading = 0;
my $writing = 0;
my $waiting = 0;

foreach (split(/\n/, $response->content)) {
  $total = $1 if (/^Active connections:\s+(\d+)/);
  if (/^Reading:\s+(\d+).*Writing:\s+(\d+).*Waiting:\s+(\d+)/) {
    $reading = $1;
    $writing = $2;
    $waiting = $3;
  }
  $requests = $3 if (/^\s+(\d+)\s+(\d+)\s+(\d+)/);
}

print "RQ:$requests; TT:$total; RD:$reading; WR:$writing; WA:$waiting\n";

exit;

