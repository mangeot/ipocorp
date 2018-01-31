#!/usr/bin/env perl -w
#-*-perl-*-
#---------------------------------------------------------------------------
# toktag_ja.pl

use strict;
use warnings;



#<s id="spar_id3147349.1">
# <w lem="0" pos="名詞-数" id="wspar_id3147349.1.1" kana="0">0</w>
# <w lem=":" pos="名詞-サ変接続" id="wspar_id3147349.1.2" kana=":">:</w>
# <w id="wspar_id3147349.1.3">通常のファイル</w>
# <w id="wspar_id3147349.1.4">。</w>
#</s></paragraph>


while (<>){
	chomp;
	if (/(<s id="[^"]+">) *$/) {
		s/ *(<s id="[^"]+">) *$/$1/;
 		print;
	}
	elsif (/<\/w> *$/) {
		s/ *<w [^>]+>([^<]+)<\/w> *$/$1/;
 		print;
	}
	else {
		print; 
		print "\n";
	}
}