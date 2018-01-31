#!/bin/sh

basedir=$(dirname $0)
workdir=/Data/Corpus
dir=${1%/}
xml='XML'
src='fr'
trg='ja'
link='link'

mkdir -p $workdir/$dir/$xml
mkdir -p $workdir/$dir/$xml/$src
mkdir -p $workdir/$dir/$xml/$trg
mkdir -p $workdir/$dir/$xml/$link
$basedir/pl/analyse_textes.pl $workdir/$dir
$basedir/pl/ajoute_texte_id.pl $workdir/$dir/$xml/$src/
$basedir/pl/ajoute_texte_id.pl $workdir/$dir/$xml/$trg/
$basedir/pl/aligne_textes.pl $workdir/$dir/$xml/
# $basedir/pl/cree_corpus_cwb.pl $dir $src $trg $workdir/$dir/$xml/link/*.xml
