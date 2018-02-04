#!/usr/bin/perl
#-*-perl-*-
#---------------------------------------------------------------------------
# cree_corpus_cwb.pl

# CQP :
# cqp -e -r /Data/Corpus_fr-ja/reg/lmd/
# show corpora;
# info LMD-FR;
# LMD-FR;
# show +lmd-ja;
# "politiques";
# control+D

use strict;
use File::Path;
use File::Basename;

my $dirname = dirname(__FILE__);

if (@ARGV< 4) {die 'usage: cree_corpus_cwb.pl CORPUS srclang trglang BITEXTS'};

my $CORPUS    = shift(@ARGV);  # corpus name
$CORPUS = lc($CORPUS);
my $srclang    = shift(@ARGV);  # source language in ISO-639-1 two letter language code
my $trglang    = shift(@ARGV);  # target language in ISO-639-1 two letter language code

#my $REGDIR    = 'reg';  # CWB registry directory
my $REGDIR    = '/usr/local/share/cwb/registry';  # CWB registry directory
#my $DATDIR    = 'dat';  # CWB data directory
my $DATDIR    = '/usr/local/share/cwb/data';

my @BITEXTS   = @ARGV;        # one or more bitext-files (XCES align)

#rmtree "$DATDIR/$CORPUS";
#`$dirname/../software/uplug/uplug-cwb/scripts/bitext-indexer.pl -o -y $CORPUS $SRCLANG $TRGLANG $REGDIR $DATDIR @BITEXTS`;
`$dirname/bitext-indexer.pl -o -y $CORPUS $srclang $trglang $REGDIR $DATDIR @BITEXTS`;

open my $in,  '<',  "$REGDIR/$CORPUS/$srclang" or die "Can't read old file: $!";
open my $out, '>', "$REGDIR/$CORPUS-$srclang" or die "Can't write new file: $!";
while( <$in> )
    {
		s/^ID   $srclang/ID $CORPUS-$srclang/;
    	s/^##:: charset  = "latin1"/##:: charset  = "utf8"/;
    	s/^##:: language = "\?\?"/##:: language = "$srclang"/;
		s/^ALIGNED $trglang/ALIGNED $CORPUS-$trglang/;

    	print $out $_;
    }
close $out;

open my $in,  '<',  "$REGDIR/$CORPUS/$trglang"      or die "Can't read old file: $!";
open my $out, '>', "$REGDIR/$CORPUS-$trglang" or die "Can't write new file: $!";
while( <$in> )
    {
		s/^ID   $trglang/ID $CORPUS-$trglang/;
    	s/^##:: charset  = "latin1"/##:: charset  = "utf8"/;
    	s/^##:: language = "\?\?"/##:: language = "$trglang"/;
		s/^ALIGNED $srclang/ALIGNED $CORPUS-$srclang/;

    	print $out $_;
    }
close $out;
rmtree "$REGDIR/$CORPUS";

rename "$DATDIR/$CORPUS/$srclang/$trglang.alx","$DATDIR/$CORPUS/$srclang/$CORPUS-$trglang.alx";
rename "$DATDIR/$CORPUS/$trglang/$srclang.alx","$DATDIR/$CORPUS/$trglang/$CORPUS-$srclang.alx";
