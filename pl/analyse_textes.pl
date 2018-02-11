#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
use File::Temp qw/ tempfile tempdir /;
use File::Copy;

my $uplug = '/usr/local/bin/uplug'; 
my $tmpdir = tempdir( CLEANUP => 1 );

if (@ARGV< 1) {die 'usage: analyse_textes.pl lang lg directory_or_files'};
my $lang    = shift(@ARGV);  # source language
my $lg    = shift(@ARGV);  # source language
my @workdirs = @ARGV;
my $xmldir = 'XML';
my $txtdir= 'TXT';

my $workdir = $workdirs[0];
chomp($workdir);

if (-d $workdir) {
	$workdir =~ s/([^\/])$/$1\//;
	my $srcd = $workdir . $txtdir . '/' . $lang .'/';
	&analyse_textes($srcd,$lg);
}
elsif (-f $workdir) {
	foreach my $fichier (@workdirs) {
		$fichier =~ /\/([^\/]+)$/;
		my $nomfichier = $1;
		analyse_fichier($fichier,$nomfichier,$lg);
	}
}

sub analyse_textes {
	my $srcdir = $_[0];
	my $lb = $_[1];
	print STDERR "analysis of dir '$srcdir'\n";
	my @LS = `ls -a '$srcdir'`;
	foreach my $file (@LS) {
	  chomp $file;
	  my $infile = $srcdir . $file;
	  if ($file =~ /\.txt$/ && $file !~ /^\./) {
		&analyse_fichier($infile,$file, $lb);
	  }
	  elsif ($file !~ /^\./ && -d $infile) {
	  		&analyse_textes($infile . '/',$lb);
	  }
	}
}

sub analyse_fichier {
	my $srcfile = $_[0];
	my $filename = $_[1];
	my $la = $_[2];
	chomp($srcfile);
	chomp($filename);
	my $outfile = $srcfile;
	$outfile =~ s%/TXT/%/XML/%;
	my $outdir = $outfile;
	$outdir =~ s%[^/]+$%%;
	`mkdir -p '$outdir'`;
	$outfile =~ s/\.txt$/\.xml/;
	$filename =~ s/\.txt$/\.xml/;
	print STDERR "Analysis of '$srcfile' to '$outfile'\n";
	`$uplug pre/$la\-all -in '$srcfile' -out '$outfile'`;
	print STDERR "Add text id in $outfile\n";
	&ajoute_texte_id($outfile, $filename);
}

sub ajoute_texte_id {
  my $file = $_[0];
  my $fileid = $_[1];
  if ($fileid =~ s/\.xml$// && $fileid !~ /^\./) {
  	$fileid =~ s/ //g;
	if ($fileid =~ /^[\-\.0-9]/) {
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
	move($tmpfilename,$file);
  }
}
