#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
my $uplug = '/usr/local/bin/uplug'; 

if (@ARGV< 1) {die 'usage: aligne_textes.pl srclang trglang directory'};
my $srclang = $ARGV[0];
my $trglang = $ARGV[1];
my $workdir = $ARGV[2];
my $link = 'links';


$workdir =~ s/([^\/])$/$1\//;

my $srcdir = $workdir . $srclang .'/';
my $trgdir = $workdir . $trglang .'/';
my $linkdir = $workdir . $link .'/';

my @LS = `ls -a $srcdir`;
foreach my $file (@LS) {
  chomp $file;
  if ($file =~ /.xml$/) {
	my $srcfile = $srcdir . $file;
	my $trgfile = $trgdir . $file;
	my $linkfile = $linkdir . $file;
	$linkfile =~ s/.xml/_$srclang\_$trglang.xml/;
	print STDERR "align $srcfile with $trgfile\n";
	`$uplug align/hun -dic $srclang-$trglang.dic -src $srcfile -trg $trgfile > $linkfile`;
  }
}
