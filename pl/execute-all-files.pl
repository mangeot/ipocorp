#!/usr/bin/env perl -w

use strict;

for (my $count =6; $count< 40; $count++) {
	my $file = $count;
	if ($count<10) {
		$file = '00' . $file;
	}
	elsif ($count<100) {
		$file = '0' . $file;
	}
	print "uplug align/sent -src fr/$file-toktag.xml -trg ja/$file-toktag.xml > links/Bible-$file\_frja.xml\n";
	`uplug align/sent -src fr/$file-toktag.xml -trg ja/$file-toktag.xml > links/Bible-$file\_frja.xml`;
}

for (my $count =101; $count< 128; $count++) {
	my $file = $count;
	print "uplug align/sent -src fr/$file-toktag.xml -trg ja/$file-toktag.xml > links/Bible-$file\_frja.xml\n";
	`uplug align/sent -src fr/$file-toktag.xml -trg ja/$file-toktag.xml > links/Bible-$file\_frja.xml`;
}