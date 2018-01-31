Description
=============

iPocorp is a corpus data warehouse. Ressources are described in XML metadata files.
These files can be used for automatic upload into the CQP Corpus WorkBench platform.

Installation
=============

The easiest way to install is to use the dockerfiles.

The iPocorp dockerfile is built upon php:apache official image: https://hub.docker.com/_/php/

Getting the latest docker image
-------------
    docker pull mangeot/ipocorp

Or building from the git repos
-------------
    docker build -t mangeot/ipolex github.com/mangeot/ipocorp

Running the docker images
-------------
Create a directory for storing the data

    mkdir -p /Users/mangeot/docker/ipocorp

And run the docker container

    docker run --name myipocorp -p 8888:80 -d --volume /Users/mangeot/docker/ipocorp:/var/www/html/Dicos mangeot/ipocorp

Using the app
-------------
    open http://localhost:8888/
