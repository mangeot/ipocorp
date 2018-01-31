#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
my $uplug = '/usr/local/bin/uplug'; 

if (@ARGV< 1) {die 'usage: ajout_texte_id.pl directory'};
my $workdir = $ARGV[0];
my $srclang= 'fr';
my $trglang= 'ja';
my $link = 'link';


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
