# Testing techniques

- [Used tools for testing](#used-tools-for-testing)
- [Requirements](#requirements)
- [PHPUnit Tests](#phpunit-tests)
- [BackstopJS](#backstopjs)
  - [Important locations in the file-system](#important-locations-in-the-file-system)
  - [Usage of the BackstopJS Docker Image](#usage-of-the-backstopjs-docker-image)
    - [Debugging BackstopJS tests](#debugging-backstopjs-tests)
- [Behat](#behat)
    - [Setup configuration for the test](#setup-the-configuration)
    - [Install Chromedriver](#install-chromedriver)
      - [Simplify the Chromedriver execution](#simplify-the-chromedriver-execution)
        - [Start Chromedriver via Bash alias](#start-chromedriver-with-a-bash-alias)
        - [Run Chromedriver by a Systemd job on Linux](#run-chromedriver-by-a-systemd-job-on-linux)
    - [Execute the Behat tests](#execute-the-behat-tests)
    - [Behat tests with extra options](#behat-tests-with-extra-options)
    - [Execute grouped Behat features by tags](#execute-grouped-behat-features-by-tags)
- [HTML validation test](#html-validation-test)
    - [Ignore validation](#ignore-validation)
    - [Further resources about the Nu Html Checker](#further-resources-about-the-nu-html-checker)
- [Twig template debugging](#twig-template-debugging)
- [Disable caching during development](#disable-caching-during-development)

## Tools used for testing

We are using the following tools for the test techniques:

- [PHPUnit](https://phpunit.de)
  - Unit tests without and with a database connection. For detailed identification of bugs in your code and building a logical application structure.
- [BackstopJS](https://github.com/garris/BackstopJS)
  - Testing against regressions in the visual layer of your webbrowser
- [Behat](https://docs.behat.org/en/latest/)
  - Testing of the application behavior in the webbrowser

### Examples

The most BackstopJS and Behat tests can be located in the following path:
```
docroot/profiles/contrib/degov/testing
```

The PHPUnit tests are attached to the modules. Some PHPUnit tests can be found here:
```
docroot/profiles/contrib/degov/modules/degov_auto_crop/tests
```

### Timely efficient development

The most developer time efficient test technique is unit testing. You should have a large amount of PHPUnit tests. There should be fewer Behat and BackstopJS tests in your application than unit tests.

Of course it takes time if you have never developed tests or if you have only limited experience in writing automated tests. If you are collecting experience and skills during the tests development process, you are minimizing the total effort in the development and maintenance of your application.

## Requirements

If you want to use [Docker](https://docs.docker.com/) to provide a deGov website instance on which you want to run the your tests against, so the `host.docker.internal` host file entry variable must be accessible. Otherwise your test will fail.

If you are using [Docker for Mac](https://docs.docker.com/docker-for-mac/) or [Docker for Windows](https://docs.docker.com/docker-for-windows/), the following steps should not be required.

For proofing if you must indeed take any setup actions, execute the console commands below.

```bash
$ nslookup host.docker.internal
Server:         192.168.10.10
Address:        192.168.10.10#53

Non-authoritative answer:
Name:   host.docker.internal
Address: 127.0.0.1
```

```bash
$ ping host.docker.internal
PING host.docker.internal (127.0.0.1) 56(84) bytes of data.
64 bytes from localhost (127.0.0.1): icmp_seq=1 ttl=64 time=0.044 ms
64 bytes from localhost (127.0.0.1): icmp_seq=2 ttl=64 time=0.058 ms
64 bytes from localhost (127.0.0.1): icmp_seq=3 ttl=64 time=0.034 ms
64 bytes from localhost (127.0.0.1): icmp_seq=4 ttl=64 time=0.042 ms
--- host.docker.internal ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3051ms
rtt min/avg/max/mdev = 0.034/0.044/0.058/0.008 ms
```

If `nslookup` and `ping` are not reporting any errors, then you do not need to do anything. Hence, if there are errors, you can proceed with the following workaround.

Add the following lines to your `/etc/hosts` file:

```bash
# Workaround deGov host.docker.internal
127.0.0.1 host.docker.internal
::1       host.docker.internal
```

The location of the DNS name `host.docker.internal` is important, because it will be used in the pipeline scripts and also provides a variable for IP of the host machine in the virtualisation context of Docker. If the IP of the host machine cannot be located, there will be errors.

## PHPUnit tests

Drupal 8 provides base classes for PHPUnit. You can inherit from them to get access to Drupal specific methods and properties. This is not required. If you like to test pure methods without any dependency to Drupal, you can write PHPUnit tests with PHPUnit only.

Examples for Drupal base classes for PHPUnit tests:

- `\Drupal\KernelTests\KernelTestBase`
- `\Drupal\KernelTests\Core\Entity\EntityKernelTestBase`
- `\Drupal\Tests\UnitTestCase`

An important place for PHPUnit is the official [PHPUnit manual](https://phpunit.readthedocs.io/en/8.4/index.html) and the official [Drupal 8 Testing](https://www.drupal.org/docs/8/testing) documentation.

Only a few helpful concepts from PHPUnit, which are part of deGov:

- [Test Doubles](https://phpunit.readthedocs.io/en/8.4/test-doubles.html)
  - [Stubs](https://phpunit.readthedocs.io/en/8.4/test-doubles.html#stubs)
  - [Mock Objects](https://phpunit.readthedocs.io/en/8.4/test-doubles.html#mock-objects)

Please note, that you should test only your own code via PHPUnit and not code which you are already expecting to work properly. The dependencies should be injected with concepts like stubs or mock objects as much as possible.

If you are inheriting from the KernelTestBase class from Drupal, you are bootstraping Drupal within your unit tests. The database connection is available also. But the duration of the tests execution process will increase massively. Therefor unit tests with as few as possible dependencies should be written as much as possible.

## BackstopJS

The official BackstopJS documentation can be found [here](https://github.com/garris/BackstopJS).

### Important locations in the file-system

There are a few crucial locations for the BackstopJS test definitions.

The reference screenshots are referenced here:
```
docroot/profiles/contrib/degov/testing/lfs_data/bitmaps_reference
```

The JSON configuration file for the comparison of the reference screenshots against the test screenshots of the current system is located here:
```
docroot/profiles/contrib/degov/testing/backstopjs/backstop.json
```

### Usage of the BackstopJS Docker image

The reference screenshots in deGov are versioned with [Git LFS](https://git-lfs.github.com/). For pulling the reference screenshots you must install the Git LFS extension in the first place.

With the following (Bash-)commands you are able to run the BackstopJS tests:

```bash
# Fetch the Docker image
cd docroot/profiles/contrib/degov/testing/
docker pull backstopjs/backstopjs

# If the DNS name "host.docker.internal" is not working, you can
# --add-host="host.docker.internal:YOUR_IP_HERE".
# Example:
docker run -it --add-host="host.docker.internal:192.168.10.10" --rm -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data backstopjs/backstopjs test

# Execute the tests
docker run -it  --rm -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data backstopjs/backstopjs test

# Update the reference screenshots
docker run -it  --rm -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data backstopjs/backstopjs reference

# Execute a test anew
docker run -it  --rm -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data backstopjs/backstopjs reference --filter "<TESTT LABEL>"

# Run tests multiple times
for ((n=0;n<10;n++)); do docker run -it  --rm -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data backstopjs/backstopjs test --filter "Verify overlay icons"; done
```

Open a shell for the Docker container, which has been created from the BackstopJS Docker image:

```bash
# One time run
docker run -it --rm -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data --entrypoint="" backstopjs/backstopjs bash
# Running container
docker exec -it NAME_or_ID bash
```

Ensure that `host.docker.internal` can be accessed from the Docker container, which has been created by the BackstopJS Docker image.

Example command:
```
ping host.docker.internal
```

Displaying the test results via the test reports from BackstopJS:

```bash
cd degov_project/docroot/profiles/contrib/degov/testing/
# Show the BackstopJS report
google-chrome-stable backstopjs/backstop_data/html_report/index.html
# or
firefox backstopjs/backstop_data/html_report/index.html
# or
chromium backstopjs/backstop_data/html_report/index.html
```

The mentioned `index.html` file contains a comparison of the reference screenshots to the test screenshots. This allows you to compare even very small differences between your expected results and the actually rendered pages. If you would approach this test manually, then you would need a "lot" more time. Especially in terms of such a complex product like deGov or websites with different content types, blocks, content entity types, forms, widgets etc.

### Debugging BackstopJS tests

For debugging failed [BackstopJS](https://github.com/garris/BackstopJS) tests (from the CI-pipeline) BackstopJS must be installed locally. It can be installed via [NPM](https://www.npmjs.com/get-npm). The following command will install BackstopJS with all it's dependencies, which are also containing the [Chromium Webbrowser](https://www.chromium.org/Home).

```bash
npm install -g backstopjs
backstop --config backstop.json test
```

Technically BackstopJS is taking screenshots from the webbrowser and is comparing them. If you like to watch the screenshot taking in the Chromium webbrowser, then change the following both entries in the configuration file, which is located at `degov/testing/backstopjs/backstop.json`:

```yml
"debug": true,
"debugWindow": true
```

## Behat

The official Behat documentation can be found [here](https://behat.org/en/latest/guides.html).

### Setup the configuration

The Behat configuration template for deGov is located here:
```
docroot/profiles/contrib/degov/testing/behat/behat.dist.yml
```

For local testing it is important to copy the provided configuration file template and make minor changes.

Example commands:
```bash
cd degov_project/
cp docroot/profiles/contrib/degov/testing/behat/behat.dist.yml behat-degov.yml
```

Modify the value of the `base_url` attribute, to suite the host url of your local Drupal instance. You "can" do the changes via [VI](https://www.howtogeek.com/102468/a-beginners-guide-to-editing-text-files-with-vi/) like below, but you are free to use the text editor of your taste:

```bash
vi behat-degov.yml
```

It should be something like in the following example:

```diff
-       base_url: http://localhost:80
---
+       base_url: http://degov-nrw.local:80
```

The configuration key `default > suites > default > paths` defines the locations to the most Behat test features. You are allowed to specify folders and/or single files. The definition can be made by a single entry or a list of them. Notice the possibilities of the [collections from the YAML format](https://yaml.org/spec/1.2/spec.html#id2759963).

Like in the current `default` or the `smoke-tests` suite, you can split your own test features into suites.

### Install Chromedriver

You need the [Chromedriver](https://chromedriver.chromium.org/) to test via Behat how the application behaves in the webbrowser.

You can download Chromedriver via the following url:

<https://chromedriver.chromium.org/downloads>

#### Simplify the Chromedriver execution

For an easier Chromedriver startup (no need to memory the parameters), you can create a startup script like `start_chromedriver.sh`, create an Bash alias or create a Systemd Job.

`start_chromedriver.sh` script example:

```bash
#!/usr/bin/env bash
chromedriver --verbose --url-base=wd/hub --port=4444
```

##### Start Chromedriver with a Bash alias

On MacOS or Linux or the Windows (by [Git Bash](https://gitforwindows.org/) or [WSL on Windows 10](https://docs.microsoft.com/en-us/windows/wsl/about) you can write a [Bash alias](https://linuxize.com/post/how-to-create-bash-aliases/) in your users Bash configuration file. The Bash configuration is inside the `.bash_profile` file mostly. It should be located in your user profile folder.

```
alias chromedriver-start='~/Dev/chromedriver --url-base=wd/hub --port=4444 --whitelisted-ips=""'
```

To activate the updated `.bash_profile` file in the current terminal session, the following command must be executed:
```
source ~/.bash_profile
```

Afterwards the Chromedriver can be started:
```
chromedriver-start
```

*A practical hint for your daily business:* An alias supports the auto-completion of commands. That means, you can type only a few characters like "chrome" and the tab-key on your keyboard will lead you to a list of possibile commands. If there are no alternative commands, the "chrome" text will be auto-completed to the only one possible command "chromedriver-start".

E.g.:
```
peter@computer degov $ chro
chromedriver-start  chroot
```


##### Run Chromedriver by a Systemd job on Linux

You can create a Systemd job on Linux, if you create the following file:
`/usr/lib/systemd/system/chromedriver.service`

In this example the Chromedriver binary is located at the following path:
`/usr/bin/chromedriver`

The content of the `chromedriver.service` file:
```
[Unit]
Description=Chromedriver Service

[Service]
Type=simple
# edit the ExecStart path to your chromdriver executable
ExecStart=/usr/bin/chromedriver --url-base=wd/hub --port=4444 --whitelisted-ips=""
KillMode=mixed

[Install]
WantedBy=multi-user.target
```

The Chromedriver can be controlled via Systemd like described in the commands below:

```bash
# start
systemctl start chromdriver.service
# status
systemctl status chromdriver.service
# stop
systemctl stop chromdriver.service
# restart
systemctl restart chromedriver.service
# enable auto start
systemctl enable chromdriver.service
# disable auto start
systemctl disable chromdriver.service
# see logs of service
journalctl -u chromedriver.service
```

### Execute the Behat tests

Ensure that Chromedriver is running in the first place:
```bash
# Start Chrome driver
chromedriver --verbose --url-base=wd/hub --port=4444
# or with a script
./start_chromedriver.sh
# or with a systemd job
systemctl start chromedriver.service
```

Now you can run all Behat tests. With the configuration from the `behat.yml` file you will be able to start Behat:
```
cd ~/var/www/degov_project
bin/behat -c behat.yml
```

#### Behat Tests with extra options

```bash
bin/behat -c behat.yml --strict -vvv --stop-on-failure
```

#### Execute grouped Behat features by tags

Add Behat tags to the test. E.g. `mytest`:
```yml
  @mytest
  Scenario: I should see the copyright block in the footer
    ....
```

Then execute the Behat with the tag:

```bash
bin/behat -c behat.yml --tags='@mytest' --strict -vvv --stop-on-failure
```

If you want to execute a single feature file, then you can define that in the `behat.yml` file with the `paths` attribute, like in the following example:

```
paths:
    - '%paths.base%/docroot/profiles/contrib/degov/testing/behat/features/bulk_action.feature'
```

If you want to execute a specific tests suite, then you can accomplish this by the `--suite=` parameter:

```
bin/behat -c behat.yml --suite=smoke-tests
```

##### Further information for executing Behat tests

All possible parameters for executing various Behat test suites of single features are described in the official [Behat documentation](https://docs.behat.org/en/v2.5/guides/6.cli.html).

## HTML validation test

If the rendering layer from Drupal is producing invalid HTML code by processing the demo content pages, then the CI-pipeline should fail. A validation error means a violation of the [HTML 5.x standard which is defined by the W3C consortium](https://dev.w3.org/html5/spec-LC/).

Please consider the [requirements](#requirements) as first.

You can use the HTML validation also locally, by executing the following console command.

```bash
bash docroot/profiles/contrib/degov/scripts/pipeline/shared_scripts/html_validation.sh
```

### Ignore validation

The validation errors don't need to be real errors. There might be [new HTML properties](https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/controlsList) which are not part of the HTML 5.x standard or are not part of HTML validation tool's functionality.

To add exceptions to the validation, you can edit the [message-filters.txt](https://github.com/validator/validator/wiki/Message-filtering#using-the-resourcesmessage-filterstxt-file) file.

You can check the current exceptions in deGov by the following file:
```bash
docroot/profiles/contrib/degov/scripts/pipeline/html_validation_shared/message-filters.txt
```

Please describe any added exception with a descriptive comment.

### Further resources about the Nu Html Checker

For HTML validation we are using the `Nu Html Checker`. Further infos about this application can be found via the following urls:

- [Nu Html Checker Docker Image](https://hub.docker.com/r/validator/validator)
- [GitHub repository](https://github.com/validator/validator)

## Twig Template Debugging

Twig is able to show you the HTML template resources for specific HTML objects on a given webpage. To accomplish that, the `docroot/sites/development.services.yml` file must be modified and the following property values must be set:

```yml
parameters:
  twig.config:
    debug: true
```

## Disable caching during development

Sometimes developers are rebuilding the entire cache of a Drupal instance manually on any code edit (e.g. via execution of the `drush cr` console command). That is not needed. Drupal enables you to disable caching entirely. Please read the following documentation page for preventing any confusion due to outdated rendering of your webpages: [Disable Drupal 8 caching during development](https://www.drupal.org/node/2598914).
