#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
use File::Temp qw/ tempfile tempdir /;
use File::Copy;

my $uplug = '/usr/local/bin/uplug'; 
my $tmpdir = tempdir( CLEANUP => 1 );

if (@ARGV< 1) {die 'usage: analyse_textes.pl lang lg directory'};
my $lang    = $ARGV[0];  # source language
my $lg    = $ARGV[1];  # source language
my $workdir = $ARGV[2];
my $xmldir = 'XML';
my $txtdir= 'TXT';


$workdir =~ s/([^\/])$/$1\//;

my $srcd = $workdir . $txtdir . '/' . $lang .'/';
my $anad = $workdir . $xmldir . '/' . $lang .'/';


&analyse_textes($srcd,$anad);

sub analyse_textes {
	my $srcdir = $_[0];
	my $trgdir = $_[1];
	print STDERR "analysis of dir '$srcdir'\n";
	my @LS = `ls -a '$srcdir'`;
	foreach my $file (@LS) {
	  chomp $file;
	  my $infile = $srcdir . $file;
	  my $analysisfile = $trgdir . $file;
	  if ($file =~ /\.txt$/ && $file !~ /^./) {
		`mkdir -p '$trgdir'`;
		$analysisfile =~ s/\.txt/\.xml/;
		print STDERR "Analysis of '$infile' to '$analysisfile'\n";
		`$uplug pre/$lg\-all-mathieu -in '$infile' -out '$analysisfile'`;
		print STDERR "Add text id in $analysisfile\n";
		&ajoute_texte_id($analysisfile, $file);
	  }
	  elsif ($file !~ /^\./ && -d $infile) {
	  		&analyse_textes($infile . '/', $analysisfile . '/');
	  }
	}
}

sub ajoute_texte_id {
  my $file = $_[0];
  my $filename = $_[1];
  my $fileid = $filename;
  if ($fileid =~ s/\.xml$// && $file !~ /^./) {
  	$fileid =~ s/ //g;
	if ($fileid =~ /^[\-.0-9]/) {
		$fileid = 't'.$fileid;
	}
	my ($tmpfh, $tmpfilename) = tempfile( DIR => $tmpdir );
	open( my $input_fh, "<", $file ) || die "Can't open $file: $!";
	while (my $line = <$input_fh>) {
		if ($line =~ /<s +id=["'][^(:'")]+['"]/) {
			$line =~ s/<s +id=(["'])/<s id=$1$fileid:/g;
		}
		$line =~ s/<text>/<text id="$fileid">/g;
		print $tmpfh $line;
	} 
	close $tmpfh;
	close $input_fh;
	print STDERR "move $tmpfilename $file\n";
	move($tmpfilename,$file);
  }
}
