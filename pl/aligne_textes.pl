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

my $srcd = $workdir . $srclang .'/';
my $trgd = $workdir . $trglang .'/';
my $linkd = $workdir . $link .'/';

&aligne_textes($srcd,$trgd, $linkd);

sub aligne_textes {
	my $srcdir = $_[0];
	my $trgdir = $_[1];
	my $linkdir = $_[2];
	`mkdir -p '$linkdir'`;
	print STDERR "analysis of dir '$srcdir'\n";
	my @LS = `ls -a '$srcdir'`;
	foreach my $file (@LS) {
	  chomp $file;
	  my $srcfile = $srcdir . $file;
	  my $trgfile = $trgdir . $file;
	  my $linkfile = $linkdir . $file;
	  if ($file =~ /\.xml$/) {
		$linkfile =~ s/.xml/_$srclang\_$trglang.xml/;
		print STDERR "align '$srcfile' with '$trgfile'\n";
		`$uplug align/hun -dic $srclang-$trglang.dic -src '$srcfile' -trg '$trgfile' > '$linkfile'`;
	  }
	  elsif ($file !~ /^\./ && -d $srcdir . $file) {
	  		&aligne_textes($srcfile . '/', $trgfile . '/',$linkfile.'/');
	  }
	}
}
