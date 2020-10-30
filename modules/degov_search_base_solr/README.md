# Setup Apache Solr core config

## Setting up Solr cores

### Run Apache Solr as a Docker container

#### Run Docker image
```
cd nrwgov_project/
docker run -p 8983:8983 --name="solr_nrwgov" -v $(pwd)/docroot/profiles/contrib/nrwgov/solr_7_config/:/own_config solr:7.7

# Or as a background deamon:
docker run -d -p 8983:8983 --name="solr_nrwgov" -v $(pwd)/docroot/profiles/contrib/nrwgov/solr_7_config/:/own_config solr:7.7

```

#### Add core
```
docker exec solr_nrwgov solr create_core -p 8983 -c degov -d /own_config
```

Please notice: See the paragraph "Adding an new Solr core" if you are going to create new Solr config. That should be
rarely needed, since the current Solr configuration is perfectly setup for working with the Solr Docker image in
version 7.7. New Solr config might be needed, if you setup an new Docker image with a different Solr version or if you
are not running the Solr Docker container, but Solr running on a different operating system etc.

#### Start exited container by Docker container name
```
docker start solr_nrwgov
```

#### List running containers
```
docker container ls
```
#### List also not running containers
```
docker container ls -a
```

#### Delete core
```
docker exec solr_nrwgov solr delete -c degov
```

### Adding an new Solr core
The core needs to be named `degov`. Therefore you firstly have to create
two directories (replace `$SOLR` and `$CORE` according to your needs):

```
mkdir $SOLR/server/solr/degov
mkdir $SOLR/server/solr/degov/conf
```

Afterwards, you have to tell SOLR about the new core by creating a
`core.properties` file:

```
echo "name=$CORE" > $SOLR/server/solr/$CORE/core.properties
```

You must download the Solr config from `/admin/config/search/search-api/server/degov`. Copy the contents to `$SOLR/server/solr/degov/conf`.

Then add the core via the administrative backend at `http://localhost:8983/solr/#/~cores`. Always add a new core after a deGov installation. Otherwise you get duplicated and weird data in your search results.

## Tweak the configuration

You must also adjust the config in the `solrcore.properties` file, to point
`solr.install.dir` to the path with the libraries executables. The default config
is the following line:
```
solr.install.dir=/usr/local/opt/solr@7.7/libexec
```

If you have e.g. installed Apache Solr 7.7.x
via [Homebrew](https://brew.sh/) on a Mac, the directory is located at `/usr/local/Cellar/solr@7.7/7.7.1/server/solr`.

Afterwards check if your config is setup correctly, by visiting the following uri in your
Drupal backend: `/admin/config/search/search-api/server/apache_solr`.

The installation is also described in `docroot/modules/contrib/search_api_solr/INSTALL.md`. Learn more about setting up Solr here: https://www.drupal.org/node/1999310.

### Re-using a Solr core
If you want to re-use a Solr core after deGov re-install, follow the following steps:
* Unload the core via [solars administrative UI in the webbrowser](http://localhost:8983/solr/#/~cores/degov)
* Remove the data directory
* Add the Solr core again. Same `name` and same `instanceDir`

## Apache Tika

For indexing document contents like from PDF or Word documents, you must configure the path to the Tika jar file at the following Drupal backend Url: `/admin/config/search/search_api_attachments`.

Define there the path to the Apache Tika Jar file, which has been previously downloaded from https://tika.apache.org/download.html. Version 1.20 of Tika is proven to work with Apache Solr in version 7.7.
