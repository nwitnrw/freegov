# Performance Testing

## Table of contents
- [Parallel Behat test runs](#parallel-behat-test-runs)
- [Measuring performance of static page visits](#measuring-performance-of-static-page-visits)

## Parallel Behat test runs
deGov is built to ensure stable page load performance for 200 concurrent users. Min. 90 percent of all pages must be loaded within 2 seconds or less. A 20:3 ratio of guests to editors is assumed, provided editors are not triggering any cache-related actions. 

We use [Robo](https://robo.li/) for running the performance check task:
```
robo degov:performance:run-tests
```
The above provides a command for running parallel Behat tests to measure performance. Make sure:
- the [Chromedriver](https://chromedriver.chromium.org/) is running
- the Chromedriver version matches the version of your Chrome web browser
- you are on a *NIX machine (MacOS or Linux) so you have the `screen` application on your system
- you have a correctly configured `behat.yml` file in your project’s root folder, which refers to the Behat context PHP classes mentioned below
- you have enabled the module `degov_behat_extension`, which provides service classes vital for running the performance tests

The command will ask you how many instances you like to run. It will execute the given amount of parallel [Behat](https://behat.org/) test runs which are configured by the `behat.yml`, simulating a user’s page visit. 

*Note:* Robo is downloaded via [Composer](https://getcomposer.org/doc/). Composer creates a symlink in the projects `bin` folder, which references the binary file from the `vendor` folder.

## Measuring performance of static page visits
The config should reference the Behat context methods from the following Behat context PHP class:
```
\Drupal\degov_behat_extension\BehatContext\PerformanceContext()
```

There are two steps provided :
- `I visit static pages and expect fulfillment of performance requirement`
  - This step is designed to ensure the performance for guest user visits. 90 percent of all guest user page loads should not take longer than 2 seconds. We are using the pages from the `degov_demo_content` Drupal module as a representable pages preset. This step also proves, that any loaded page does not contain any errors (e.g. PHP errors or warnings). You will also receive details about the page load time of all pages and the percentage of successful pages within your Behat command output. This will help you during the debugging process.
- `I visit static pages`
  - This step does everything what is described above, except the page load duration check. It is designed to ensure page loads without errors. The load duration performance is not measured here, because this step is designed to prove editor page visits, which are not provided from drupal's (dynamic) page cache only.

For further reference, see also the Behat features located in `profiles/contrib/degov/modules/degov_behat_extension/tests/src/Behat/features/performance`.