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

if (@ARGV< 2) {die 'usage: cree_corpus_cwb.pl CORPUS srclang trglang BITEXTS'};

my $CORPUS    = shift(@ARGV);  # corpus name
$CORPUS = lc($CORPUS);
my $SRCLANG    = shift(@ARGV);  # source language
my $TRGLANG    = shift(@ARGV);  # target language

#my $REGDIR    = 'reg';  # CWB registry directory
my $REGDIR    = '/usr/local/share/cwb/registry';  # CWB registry directory
#my $DATDIR    = 'dat';  # CWB data directory
my $DATDIR    = '/usr/local/share/cwb/data';

my @BITEXTS   = @ARGV;        # one or more bitext-files (XCES align)

rmtree "$DATDIR/$CORPUS";
`$dirname/../software/uplug/uplug-cwb/scripts/bitext-indexer.pl $CORPUS $SRCLANG $TRGLANG $REGDIR $DATDIR @BITEXTS`;

open my $in,  '<',  "$REGDIR/$CORPUS/fr" or die "Can't read old file: $!";
open my $out, '>', "$REGDIR/$CORPUS-fr" or die "Can't write new file: $!";
while( <$in> )
    {
		s/^ID   fr/ID $CORPUS-fr/;
    	s/^##:: charset  = "latin1"/##:: charset  = "utf8"/;
    	s/^##:: language = "\?\?"/##:: language = "fr"/;
		s/^ALIGNED ja/ALIGNED $CORPUS-ja/;

    	print $out $_;
    }
close $out;

open my $in,  '<',  "$REGDIR/$CORPUS/ja"      or die "Can't read old file: $!";
open my $out, '>', "$REGDIR/$CORPUS-ja" or die "Can't write new file: $!";
while( <$in> )
    {
		s/^ID   ja/ID $CORPUS-ja/;
    	s/^##:: charset  = "latin1"/##:: charset  = "utf8"/;
    	s/^##:: language = "\?\?"/##:: language = "ja"/;
		s/^ALIGNED fr/ALIGNED $CORPUS-fr/;

    	print $out $_;
    }
close $out;
rmtree "$REGDIR/$CORPUS";

rename "$DATDIR/$CORPUS/fr/ja.alx","$DATDIR/$CORPUS/fr/$CORPUS-ja.alx";
rename "$DATDIR/$CORPUS/ja/fr.alx","$DATDIR/$CORPUS/ja/$CORPUS-fr.alx";
