#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
use File::Temp qw/ tempfile tempdir /;
use File::Copy;

my $tmpdir = tempdir( CLEANUP => 1 );
if (@ARGV< 1) {die 'usage: ajout_texte_id.pl directory'};
my $workdir = $ARGV[0];
$workdir =~ s/([^\/])$/$1\//;

my @LS = `ls -a $workdir`;
foreach my $file (@LS) {
  chomp $file;
  my $fileid = $file;
  if ($fileid =~ s/.xml$//) {
	if ($fileid =~ /^[\-.0-9]/) {
		$fileid = 't'.$fileid;
	}
	$file = $workdir . $file;
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
