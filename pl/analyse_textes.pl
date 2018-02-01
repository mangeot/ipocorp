#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
my $uplug = '/usr/local/bin/uplug'; 

if (@ARGV< 1) {die 'usage: analyse_textes.pl lang directory'};
my $lang    = $ARGV[0];  # source language
my $workdir = $ARGV[1];
my $xmldir = 'XML';
my $txtdir= 'TXT';
my $link = 'link';


$workdir =~ s/([^\/])$/$1\//;

my $srcdir = $workdir . $txtdir . '/' . $lang .'/';
my $anadir = $workdir . $xmldir . '/' . $lang .'/';

my @LS = `ls -a $srcdir`;
foreach my $file (@LS) {
  chomp $file;
  if ($file =~ /\.txt$/) {
	my $infile = $srcdir . $file;
	my $analysisfile = $anadir . $file;
	$analysisfile =~ s/\.txt/\.xml/;
	print STDERR "analysis of $infile to $analysisfile\n";
	`$uplug pre/$lang\-all-mathieu -in $infile -out $analysisfile`;
  }
}