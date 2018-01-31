#!/usr/bin/env perl -w
#-*-perl-*-
#---------------------------------------------------------------------------
# toktag_ja.pl

use strict;
use warnings;

my $dirja = shift(@ARGV);

my $remove_w

&toktag_dir($dirja);

exit 0;
    
sub toktag_dir {
    	my $dir = shift(@_);
    	my @dirs;
    	print "toktag $dir\n";
    	opendir(DIR, "$dir") or die $!;
		while (my $doc = readdir(DIR)) {
        		next if ($doc =~ m/^\./);
        		if (-d "$dir/$doc") {
        			push @dirs, "$dir/$doc";
        		}
        		elsif (-f "$dir/$doc") {
        			my $ext = '';
        			if ($doc =~ /\.gz$/) {
        				$doc =~ s/\.gz$//;
        				$ext = '.gz';
        				`gunzip $dir/$doc$ext`;	
        			}
#					`/Data/Corpus_fr-ja/remove_word_tags.pl < $dir/$doc > $dir/$doc\.wow`;
#					rename("$dir/$doc\.wow","$dir/$doc");
        			print "uplug pre/ja/toktag -in $dir/$doc -out $dir/$doc\.out\n";
        			`uplug pre/ja/toktag -in $dir/$doc -out $dir/$doc\.out`;
					unlink("$dir/$doc");
					rename("$dir/$doc\.out","$dir/$doc");
					print "gzip $dir/$doc\n";
					`gzip $dir/$doc`;
        		}
		}
		closedir(DIR);
		for my $dir (@dirs) {
			&toktag_dir($dir);
		}
    }
