## Html Validation test

In case Drupal renders invalid HTML code (within our demo content pages) the pipeline should fail.

The test uses all pages which are defined inside the [backstop.json](../../testing/backstopjs/backstop.json) and depend on the domain defined there (http://host.docker.internal).

Technically html_validation.sh does the following:

* Get all urls from backstop.json which don't have a `skipValidation` property.
* Run Wget on all these urls and save the code into HTML files
* Run [nu valudator](https://validator.w3.org/nu) docker image (validator/validator:latest) on the HTML files.

All errors will be listed under the output of `Picked up JAVA_TOOL_OPTIONS:`.

Example output:

```
### Validating HTML5
...
Total wall clock time: 35s
Downloaded: 18 files, 3.0M in 0.1s (21.7 MB/s)
Picked up JAVA_TOOL_OPTIONS:
"file:/files/page-text-paragraph-sidebar":1945.7-1945.73: error: Duplicate ID “paragraph_text_3”.
Found some validation errors.
```

Schema: "file:/files/FILENAME":LINENR.COLUMNNR-LINENR.COLUMNNR: error: ERRORMESSAGE.

You can also use HTML validation locally for development purposes, but alternatively you may just paste at [w3c page](https://validator.w3.org/nu/#textarea)).

### Requirements for local testing


* Installed [docker](https://docs.docker.com/install/)
* wget (GNU Version)
* [jq](https://stedolan.github.io/jq/)
* Drupal instance must be reachable under domain http://host.docker.internal

#### MacOS

* Use [Docker for mac](https://docs.docker.com/docker-for-mac/install/) to get Docker and [homebrew](https://brew.sh) to get the rest of the requirements

```
brew install wget jq
```

### Usage

* Just run the script via CLI

deGov:

```bash
bash docroot/profiles/contrib/degov/scripts/pipeline/shared_scripts/html_validation.sh
```

nrwGov:

```bash
cd docroot/profiles/contrib/nrwgov/scripts/pipeline && \
ln -s ../../../degov/scripts/pipeline/shared_scripts/
bash shared_scripts/html_validation.sh
```

Mind your cache

wget is a "annonymous" user to Drupal and it respects cache headers.
To make sure you get the current HTML you have to clear the cache!


#### Ignoring validations

Not all validation errors necessarily are real errors. E.g. some errors are [features](https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/controlsList). New properties which didn't make it in to the standard or validator tool yet.

To deal with that you can add exceptions to the nu validator [message-filters.txt](https://github.com/validator/validator/wiki/Message-filtering#using-the-resourcesmessage-filterstxt-file).

