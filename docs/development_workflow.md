# Development workflow

## Git branching model

We work with a modified version of the [Git flow branching model](https://danielkummer.github.io/git-flow-cheatsheet/). We do not use a
"develop" branch.

### Release branches
Before a release is created, feature-branches are merged into the release dev-branch. Release branches are named according to this pattern: 
```
release/MAJOR-VERSION.(MINOR-VERSION.)x-dev
```

Example names for release branches:

- release/2.x
- release/2.1.x
- release/2.1.x-dev

### Tagging release branches
New versions are tagged on release branches. Whereas feature branches are closed when they are merged, release branches are never closed.

### Feature branches
Feature branch names follow this pattern: feature/DRUPAL.ORG-ISSUE-ID-short-description

Examples:
- feature/DRUPAL.ORG-ISSUE-ID-short-description
- feature/2353454-fixed-button-red-color

### Hotfixes
For hotfixes we use hotfix branches. Our branches must have one of the three prefixes: release, feature or hotfix. Hotfixes are merged into the release branch. The release branch is then later merged into the release-dev branch. 

We use the master branch for our platform.sh test instances. Platform.sh uses this branch for the creation of new server instances.

If your Git branch names do not match the conventions, you could stumble
into [issues with Composer](https://getcomposer.org/doc/articles/versions.md#branches).

## Steps to prepare a feature branch to be merged

### Preferred way: Use our Bitbucket Git repository
1. Fork the [deGov project on Bitbucket](https://bitbucket.org/publicplan/degov) and enable pipelines for it. Or
ask us for write permission to our Git repository.
2. Implement your changes via a Git branch. Every Git branch must have a relation to an issue on Drupal.org. Please use our issue queue.
3. Write an automated test whenever possible. We won't commit any complex changes without automated tests!
4. Set your issue status firstly to "Needs review".
5. The Bitbucket CI pipeline must run successfully to make sure that our tests pass successfully.
6. Afterwards it will be set to "Fixed", if your changes are commited into the release branch. Your changes will be stored in the release issue and scheduled for the next release.

#### Automated tests for making sure there are no regressions
We use the following tools to make sure, that our development
work won't hurt work we have done earlier.

- [BackstopJS](https://github.com/garris/BackstopJS): Visual regressions tests
- [PHPUnit](https://phpunit.de/): Unit and functional tests, which are focusing code
- [Behat](https://behat.org/en/latest/): Ensures that the application level behaves like expected. Like
forms and widgets within the web browser and APIs which can be reached
via HTTP.
    - [Behat Drupal extension](https://www.drupal.org/project/drupalextension)
    
### Alternative: Create a patch and post it into the issue queue

#### Dorgflow: Automated Git workflow for drupal.org patches.
This is the preferred way, because you can easily type in the issue ID
as a console parameter and [Dorgflow](https://github.com/joachim-n/dorgflow) will do all the Git patch and
interdiff work for you.

#### Not recommended: the manual way

##### Creating the patch
1. Implement your changes locally and create a patch:
Clone the deGov Git repository and create a new Git branch: 
```
git checkout -b BRANCH-WITH-CHANGES`
```
2. Do your changes and commit:
```
git commit -am "My changes."
```
3. Now create the patch file by a diff from your new branch and the master branch to extract the changes:
```
git diff release/2.x BRANCH-WITH-CHANGES > EXTRACTED-CHANGES.patch
```
4. To test your patch, you can switch back to your master branch and apply the patch (the changes will not be commited, until you commit them in another Git command):
```
git apply EXTRACTED-CHANGES.patch
```

##### Create the interdiff
If you have modified an existing patch within an issue, you must [create
an interdiff](https://www.drupal.org/documentation/git/interdiff) to make your changes visible.