#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------

use strict;
my $uplug = '/usr/local/bin/uplug'; 

if (@ARGV< 1) {die 'usage: aligne_textes.pl srclang trglang sr tr directory_or_files'};
my $srclang = shift(@ARGV);
my $trglang = shift(@ARGV);
my $sr = shift(@ARGV);
my $tr = shift(@ARGV);
my @workdirs = @ARGV;
my $workdir = $workdirs[0];
my $link = 'links';

if (-d $workdir) {
	$workdir =~ s/([^\/])$/$1\//;

	my $srcd = $workdir . $srclang .'/';
	my $trgd = $workdir . $trglang .'/';
	my $linkd = $workdir . $link .'/';

	&aligne_textes($srcd,$trgd, $linkd);
}
elsif (-f $workdir) {
	foreach my $file (@workdirs) {
		if (-f $file && $file =~ /\/XML\// && $file =~ /\.xml$/) {
			my $srcfile = $file;
			my $trgfile = $file;
			my $linkfile = $file;
			if ($file =~ /\/XML\/$srclang\//) {
				$trgfile =~ s%/XML/$srclang/%/XML/$trglang/%;
			}
			elsif ($file =~ /\/XML\/$trglang\//) {
				$srcfile =~ s%/XML/$trglang/%/XML/$srclang/%;
				$linkfile = $srcfile;
			}
			else {
				$srcfile ='';
			}
			if (-f $srcfile && -f $trgfile) {
				$linkfile =~ s/\.xml$/_$srclang\_$trglang.xml/;
				$linkfile =~ s%/XML/$srclang/%/XML/$link/%;
				my $linkdir = $linkfile;
				$linkdir =~ s%[^/]+$%%;
				`mkdir -p '$linkdir'`;
				&aligne_fichiers($srcfile,$trgfile,$linkfile,$sr,$tr);
			}
		}
	}
}

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
	  if ($file =~ /\.xml$/ && -f $srcfile && -f $trgfile) {
		$linkfile =~ s/\.xml$/_$srclang\_$trglang.xml/;
		&aligne_fichiers($srcfile,$trgfile,$linkfile,$sr,$tr);
	  }
	  elsif ($file !~ /^\./ && -d $srcdir . $file) {
	  		&aligne_textes($srcfile . '/', $trgfile . '/',$linkfile.'/');
	  }
	}
}

sub aligne_fichiers {
	my $srcfichier = $_[0];
	my $trgfichier = $_[1];
	my $linkfichier = $_[2];
	my $sl = $_[3];
	my $tl = $_[4];
	print STDERR "align '$srcfichier' with '$trgfichier'\n";
	`$uplug align/hun -dic $sl-$tl.dic -src '$srcfichier' -trg '$trgfichier' > '$linkfichier'`;
}