#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
my $uplug = '/usr/local/bin/uplug'; 

if (@ARGV< 1) {die 'usage: analyse_textes.pl directory'};
my $workdir = $ARGV[0];
my $xmldir = 'XML';
my $txtdir= 'TXT';
my $srclang= 'fr';
my $trglang= 'ja';
my $link = 'link';


$workdir =~ s/([^\/])$/$1\//;

my $srcdir = $workdir . $txtdir . '/' . $srclang .'/';
my $trgdir = $workdir . $txtdir . '/' . $trglang .'/';
my $anasrcdir = $workdir . $xmldir . '/' . $srclang .'/';
my $anatrgdir = $workdir . $xmldir . '/' . $trglang .'/';

my @LS = `ls -a $srcdir`;
foreach my $file (@LS) {
  chomp $file;
  if ($file =~ /\.txt$/) {
	my $infile = $srcdir . $file;
	my $analysisfile = $anasrcdir . $file;
	$analysisfile =~ s/\.txt/\.xml/;
	print STDERR "analysis of $infile to $analysisfile\n";
	`$uplug pre/$srclang\-all-mathieu -in $infile -out $analysisfile`;
  }
}

@LS = `ls -a $trgdir`;
foreach my $file (@LS) {
  chomp $file;
  if ($file =~ /\.txt$/) {
	my $infile = $trgdir . $file;
	my $analysisfile = $anatrgdir . $file;
	$analysisfile =~ s/\.txt/\.xml/;
	print STDERR "analysis of $infile to $analysisfile\n";
	`$uplug pre/$trglang\-all-mathieu -in $infile -out $analysisfile`;
  }
}
