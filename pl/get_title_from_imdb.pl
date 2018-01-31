#!/usr/bin/perl -w

use strict;
use utf8::all;
use Encode 'encode';
use JSON;
use Data::Dumper;

#my $opensubtitleid = $ARGV[0];

#my $opensubtitlepage = `curl https://www.opensubtitles.org/en/subtitles/$opensubtitleid`;
local $/;
my $opensubtitlepage = <STDIN>;

$opensubtitlepage =~ /href="http:\/\/www.imdb.com\/title\/(tt[0-9]+)\//;
my $imdbid = $1;

my $omdbanswer = `curl 'http://www.omdbapi.com/?apikey=87ec331c&i=$imdbid'`;
my $json_bytes = encode('UTF-8', $omdbanswer);
my $json = JSON->new->utf8->decode($json_bytes);

print '<html>
<head>
 <meta charset="UTF-8" />
 <title>Référence bibliographique</title>
</head>
<body>
 <p class="corpus">Corpus : OpenSubtitles</p>
 <p class="titre">Titre : ',$json->{'Title'},'</p>
 <p class="an">Année : ',$json->{'Year'},'</p>
 <p class="langues">Langues : ',$json->{'Language'},'</p>
 <p class="genre">Genre : ',$json->{'Genre'},'</p>
 <p class="site">Site du film : <a href="',$json->{'Website'},'">',$json->{'Website'},'</a></p>
 <p class="site">Page sur IMDB : <a href="http://www.imdb.com/title/',$json->{'imdbID'},'">http://www.imdb.com/title/',$json->{'imdbID'},'</a></p>
</body>
</html>';




